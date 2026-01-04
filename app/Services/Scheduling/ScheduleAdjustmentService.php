<?php

namespace App\Services\Scheduling;

use App\Models\Appointment;
use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ScheduleAdjustmentService
{
    protected ScheduleConflictDetector $conflictDetector;

    public function __construct(ScheduleConflictDetector $conflictDetector)
    {
        $this->conflictDetector = $conflictDetector;
    }

    /**
     * Reschedule an appointment with transaction safety
     */
    public function reschedule(
        Appointment $appointment,
        Carbon $newStart,
        ?Carbon $newEnd = null,
        ?int $newTechnicianId = null
    ): array {
        $duration = $appointment->scheduled_start_at->diffInMinutes($appointment->scheduled_end_at);
        $newEnd = $newEnd ?? $newStart->copy()->addMinutes($duration);

        $technician = $newTechnicianId
            ? User::findOrFail($newTechnicianId)
            : $appointment->assignedTo;

        // Validate new time slot
        $validation = $this->conflictDetector->validateAssignment(
            $appointment->workOrder,
            $technician,
            $newStart,
            $duration
        );

        if (!$validation['can_proceed']) {
            return [
                'success' => false,
                'message' => 'Cannot reschedule due to conflicts',
                'conflicts' => $validation['conflicts'],
                'alternatives' => $this->conflictDetector->suggestAlternatives(
                    $technician,
                    $newStart,
                    $duration
                ),
            ];
        }

        // Perform reschedule with lock
        return DB::transaction(function () use ($appointment, $newStart, $newEnd, $newTechnicianId, $validation) {
            // Lock the appointment row
            $appointment = Appointment::lockForUpdate()->find($appointment->id);

            $oldStart = $appointment->scheduled_start_at;
            $oldEnd = $appointment->scheduled_end_at;
            $oldTechnicianId = $appointment->assigned_to_user_id;

            $appointment->update([
                'scheduled_start_at' => $newStart,
                'scheduled_end_at' => $newEnd,
                'assigned_to_user_id' => $newTechnicianId ?? $oldTechnicianId,
            ]);

            // Update work order if linked
            if ($appointment->workOrder) {
                $appointment->workOrder->update([
                    'scheduled_start_at' => $newStart,
                    'scheduled_end_at' => $newEnd,
                    'assigned_to_user_id' => $newTechnicianId ?? $oldTechnicianId,
                ]);
            }

            return [
                'success' => true,
                'appointment' => $appointment->fresh(),
                'changes' => [
                    'start' => ['from' => $oldStart, 'to' => $newStart],
                    'end' => ['from' => $oldEnd, 'to' => $newEnd],
                    'technician_id' => ['from' => $oldTechnicianId, 'to' => $newTechnicianId ?? $oldTechnicianId],
                ],
                'warnings' => $validation['has_warning'] ? $validation['conflicts'] : [],
            ];
        });
    }

    /**
     * Swap appointments between two technicians
     */
    public function swapAppointments(Appointment $appointment1, Appointment $appointment2): array
    {
        // Validate both swaps
        $validation1 = $this->conflictDetector->validateAssignment(
            $appointment1->workOrder,
            $appointment2->assignedTo,
            $appointment1->scheduled_start_at,
            $appointment1->scheduled_start_at->diffInMinutes($appointment1->scheduled_end_at)
        );

        $validation2 = $this->conflictDetector->validateAssignment(
            $appointment2->workOrder,
            $appointment1->assignedTo,
            $appointment2->scheduled_start_at,
            $appointment2->scheduled_start_at->diffInMinutes($appointment2->scheduled_end_at)
        );

        if (!$validation1['can_proceed'] || !$validation2['can_proceed']) {
            return [
                'success' => false,
                'message' => 'Swap not possible due to conflicts',
                'conflicts' => [
                    'appointment1' => $validation1['conflicts'],
                    'appointment2' => $validation2['conflicts'],
                ],
            ];
        }

        return DB::transaction(function () use ($appointment1, $appointment2) {
            $tech1Id = $appointment1->assigned_to_user_id;
            $tech2Id = $appointment2->assigned_to_user_id;

            // Lock and swap
            $a1 = Appointment::lockForUpdate()->find($appointment1->id);
            $a2 = Appointment::lockForUpdate()->find($appointment2->id);

            $a1->update(['assigned_to_user_id' => $tech2Id]);
            $a2->update(['assigned_to_user_id' => $tech1Id]);

            // Update linked work orders
            if ($a1->workOrder) {
                $a1->workOrder->update(['assigned_to_user_id' => $tech2Id]);
            }
            if ($a2->workOrder) {
                $a2->workOrder->update(['assigned_to_user_id' => $tech1Id]);
            }

            return [
                'success' => true,
                'message' => 'Appointments swapped successfully',
                'appointment1' => $a1->fresh(),
                'appointment2' => $a2->fresh(),
            ];
        });
    }

    /**
     * Bulk reschedule multiple appointments
     */
    public function bulkReschedule(array $rescheduleData): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        DB::transaction(function () use ($rescheduleData, &$results) {
            foreach ($rescheduleData as $item) {
                $appointment = Appointment::find($item['appointment_id']);
                if (!$appointment) {
                    $results['failed'][] = [
                        'appointment_id' => $item['appointment_id'],
                        'reason' => 'Appointment not found',
                    ];
                    continue;
                }

                $result = $this->reschedule(
                    $appointment,
                    Carbon::parse($item['new_start']),
                    isset($item['new_end']) ? Carbon::parse($item['new_end']) : null,
                    $item['new_technician_id'] ?? null
                );

                if ($result['success']) {
                    $results['success'][] = $result['appointment'];
                } else {
                    $results['failed'][] = [
                        'appointment_id' => $item['appointment_id'],
                        'reason' => $result['message'],
                        'conflicts' => $result['conflicts'] ?? [],
                    ];
                }
            }
        });

        return $results;
    }

    /**
     * Compress schedule to fill gaps
     */
    public function compressSchedule(User $technician, Carbon $date): array
    {
        $appointments = Appointment::where('assigned_to_user_id', $technician->id)
            ->whereDate('scheduled_start_at', $date)
            ->where('status', 'scheduled')
            ->orderBy('scheduled_start_at')
            ->get();

        if ($appointments->count() < 2) {
            return [
                'success' => true,
                'message' => 'No compression needed',
                'changes' => [],
                'time_saved' => 0,
            ];
        }

        $changes = [];
        $totalTimeSaved = 0;
        $workdayStart = $date->copy()->setTime(8, 0);
        $currentTime = $workdayStart;
        $minGap = 15; // Minimum 15 minutes between appointments

        return DB::transaction(function () use ($appointments, &$changes, &$totalTimeSaved, $currentTime, $minGap) {
            foreach ($appointments as $appointment) {
                $duration = $appointment->scheduled_start_at->diffInMinutes($appointment->scheduled_end_at);
                $originalStart = $appointment->scheduled_start_at->copy();

                // If current time is earlier than original, we can compress
                if ($currentTime->lt($originalStart->subMinutes($minGap))) {
                    $newStart = $currentTime->copy();
                    $newEnd = $newStart->copy()->addMinutes($duration);

                    $gapSaved = $originalStart->diffInMinutes($newStart);
                    if ($gapSaved > $minGap) {
                        $totalTimeSaved += ($gapSaved - $minGap);

                        $appointment->update([
                            'scheduled_start_at' => $newStart,
                            'scheduled_end_at' => $newEnd,
                        ]);

                        $changes[] = [
                            'appointment_id' => $appointment->id,
                            'original_start' => $originalStart->format('g:i A'),
                            'new_start' => $newStart->format('g:i A'),
                            'gap_removed' => $gapSaved - $minGap,
                        ];
                    }
                }

                $currentTime = Carbon::parse($appointment->scheduled_end_at)->addMinutes($minGap);
            }

            return [
                'success' => true,
                'message' => "Schedule compressed, saved {$totalTimeSaved} minutes",
                'changes' => $changes,
                'time_saved' => $totalTimeSaved,
            ];
        });
    }

    /**
     * Emergency insertion - bump lower priority appointments
     */
    public function emergencyInsert(
        WorkOrder $emergencyWorkOrder,
        User $technician,
        Carbon $targetTime
    ): array {
        $duration = $emergencyWorkOrder->estimated_minutes ?? 60;
        $endTime = $targetTime->copy()->addMinutes($duration);

        // Find conflicting appointments
        $conflicts = $this->conflictDetector->findOverlappingAppointments(
            $technician,
            $targetTime,
            $endTime,
            $emergencyWorkOrder->id
        );

        if ($conflicts->isEmpty()) {
            // No conflicts, just insert
            return $this->createEmergencyAppointment($emergencyWorkOrder, $technician, $targetTime, $duration);
        }

        // Calculate bump options
        $bumpOptions = [];

        foreach ($conflicts as $conflict) {
            $conflictWorkOrder = $conflict->workOrder;
            $conflictPriority = $conflictWorkOrder?->priority ?? 'standard';
            $emergencyPriority = $emergencyWorkOrder->priority;

            // Check if emergency has higher priority
            $priorityRanks = ['low' => 1, 'standard' => 2, 'high' => 3, 'urgent' => 4, 'emergency' => 5];
            $canBump = ($priorityRanks[$emergencyPriority] ?? 4) > ($priorityRanks[$conflictPriority] ?? 2);

            if ($canBump) {
                // Find alternative time for bumped appointment
                $alternatives = $this->conflictDetector->suggestAlternatives(
                    $technician,
                    $conflict->scheduled_end_at,
                    $conflict->scheduled_start_at->diffInMinutes($conflict->scheduled_end_at)
                );

                $bumpOptions[] = [
                    'appointment' => $conflict,
                    'work_order' => $conflictWorkOrder,
                    'can_bump' => true,
                    'alternative_times' => $alternatives,
                ];
            } else {
                $bumpOptions[] = [
                    'appointment' => $conflict,
                    'work_order' => $conflictWorkOrder,
                    'can_bump' => false,
                    'reason' => 'Equal or higher priority',
                ];
            }
        }

        return [
            'success' => false,
            'requires_confirmation' => true,
            'message' => 'Conflicts found that require resolution',
            'conflicts' => $bumpOptions,
            'emergency_work_order' => $emergencyWorkOrder,
            'target_time' => $targetTime,
        ];
    }

    /**
     * Confirm emergency insertion with bumps
     */
    public function confirmEmergencyInsert(
        WorkOrder $emergencyWorkOrder,
        User $technician,
        Carbon $targetTime,
        array $bumpDecisions
    ): array {
        return DB::transaction(function () use ($emergencyWorkOrder, $technician, $targetTime, $bumpDecisions) {
            $bumped = [];

            foreach ($bumpDecisions as $decision) {
                $appointment = Appointment::find($decision['appointment_id']);
                if (!$appointment)
                    continue;

                if ($decision['action'] === 'reschedule' && isset($decision['new_time'])) {
                    $newStart = Carbon::parse($decision['new_time']);
                    $duration = $appointment->scheduled_start_at->diffInMinutes($appointment->scheduled_end_at);

                    $appointment->update([
                        'scheduled_start_at' => $newStart,
                        'scheduled_end_at' => $newStart->copy()->addMinutes($duration),
                    ]);

                    $bumped[] = [
                        'appointment' => $appointment->fresh(),
                        'action' => 'rescheduled',
                        'new_time' => $newStart,
                    ];
                } elseif ($decision['action'] === 'reassign' && isset($decision['new_technician_id'])) {
                    $appointment->update([
                        'assigned_to_user_id' => $decision['new_technician_id'],
                    ]);

                    $bumped[] = [
                        'appointment' => $appointment->fresh(),
                        'action' => 'reassigned',
                        'new_technician_id' => $decision['new_technician_id'],
                    ];
                }
            }

            // Create the emergency appointment
            $result = $this->createEmergencyAppointment(
                $emergencyWorkOrder,
                $technician,
                $targetTime,
                $emergencyWorkOrder->estimated_minutes ?? 60
            );

            $result['bumped_appointments'] = $bumped;

            return $result;
        });
    }

    /**
     * Create emergency appointment
     */
    protected function createEmergencyAppointment(
        WorkOrder $workOrder,
        User $technician,
        Carbon $startTime,
        int $duration
    ): array {
        return DB::transaction(function () use ($workOrder, $technician, $startTime, $duration) {
            $appointment = Appointment::create([
                'work_order_id' => $workOrder->id,
                'assigned_to_user_id' => $technician->id,
                'scheduled_start_at' => $startTime,
                'scheduled_end_at' => $startTime->copy()->addMinutes($duration),
                'status' => 'scheduled',
                'notes' => 'Emergency insertion',
            ]);

            $workOrder->update([
                'assigned_to_user_id' => $technician->id,
                'scheduled_start_at' => $startTime,
                'scheduled_end_at' => $startTime->copy()->addMinutes($duration),
                'status' => 'assigned',
            ]);

            return [
                'success' => true,
                'appointment' => $appointment,
                'work_order' => $workOrder->fresh(),
                'message' => 'Emergency appointment created',
            ];
        });
    }

    /**
     * Analyze impact of a proposed change
     */
    public function analyzeImpact(
        Appointment $appointment,
        Carbon $newStart,
        ?int $newTechnicianId = null
    ): array {
        $duration = $appointment->scheduled_start_at->diffInMinutes($appointment->scheduled_end_at);
        $newEnd = $newStart->copy()->addMinutes($duration);

        $technician = $newTechnicianId
            ? User::find($newTechnicianId)
            : $appointment->assignedTo;

        if (!$technician) {
            return ['error' => 'Technician not found'];
        }

        // Get current technician's schedule impact
        $currentTechSchedule = $this->getScheduleSnapshot($appointment->assignedTo, $appointment->scheduled_start_at);

        // Get new technician's schedule impact
        $newTechSchedule = $this->getScheduleSnapshot($technician, $newStart);

        // Validate the change
        $validation = $this->conflictDetector->validateAssignment(
            $appointment->workOrder,
            $technician,
            $newStart,
            $duration
        );

        return [
            'proposed_change' => [
                'from' => [
                    'technician' => $appointment->assignedTo?->name,
                    'start' => $appointment->scheduled_start_at->format('M j, g:i A'),
                    'end' => $appointment->scheduled_end_at->format('g:i A'),
                ],
                'to' => [
                    'technician' => $technician->name,
                    'start' => $newStart->format('M j, g:i A'),
                    'end' => $newEnd->format('g:i A'),
                ],
            ],
            'validation' => $validation,
            'impact' => [
                'original_technician' => [
                    'name' => $appointment->assignedTo?->name,
                    'jobs_before' => $currentTechSchedule['job_count'],
                    'jobs_after' => $currentTechSchedule['job_count'] - 1,
                    'utilization_change' => '-' . round(($duration / ($appointment->assignedTo?->max_daily_minutes ?? 480)) * 100) . '%',
                ],
                'new_technician' => [
                    'name' => $technician->name,
                    'jobs_before' => $newTechSchedule['job_count'],
                    'jobs_after' => $newTechSchedule['job_count'] + 1,
                    'utilization_change' => '+' . round(($duration / ($technician->max_daily_minutes ?? 480)) * 100) . '%',
                ],
            ],
            'customer_impact' => [
                'original_time' => $appointment->scheduled_start_at->format('g:i A'),
                'new_time' => $newStart->format('g:i A'),
                'time_difference' => $appointment->scheduled_start_at->diffForHumans($newStart, ['parts' => 2]),
            ],
            'can_proceed' => $validation['can_proceed'],
            'warnings' => $validation['conflicts']->where('severity', 'warning')->values(),
        ];
    }

    /**
     * Get schedule snapshot for a technician
     */
    protected function getScheduleSnapshot(User $technician, Carbon $date): array
    {
        $appointments = Appointment::where('assigned_to_user_id', $technician->id)
            ->whereDate('scheduled_start_at', $date)
            ->get();

        $totalMinutes = $appointments->sum(function ($a) {
            return $a->scheduled_start_at->diffInMinutes($a->scheduled_end_at);
        });

        return [
            'job_count' => $appointments->count(),
            'total_minutes' => $totalMinutes,
            'utilization' => round(($totalMinutes / ($technician->max_daily_minutes ?? 480)) * 100),
        ];
    }
}
