<?php

namespace App\Services\Scheduling;

use App\Models\Equipment;
use App\Models\User;
use Carbon\Carbon;
use Zap\Facades\Zap;

/**
 * Advanced scheduling features including conflict detection,
 * equipment maintenance, and emergency overrides.
 */
class AdvancedSchedulingService
{
    public function __construct(
        protected TechnicianScheduleService $technicianService
    ) {
    }

    /**
     * Check for conflicts before booking.
     *
     * @return array{has_conflict: bool, conflicts: array}
     */
    public function checkForConflicts(
        User $technician,
        string $date,
        string $startTime,
        string $endTime
    ): array {
        $conflicts = [];

        // Get existing schedules for this date
        $existingSchedules = $technician->schedules()
            ->with('periods')
            ->where(function ($query) use ($date) {
                $query->whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date);
            })
            ->whereIn('type', ['appointment', 'blocked'])
            ->get();

        $requestedStart = Carbon::parse($startTime);
        $requestedEnd = Carbon::parse($endTime);

        foreach ($existingSchedules as $schedule) {
            foreach ($schedule->periods as $period) {
                $periodStart = Carbon::parse($period->start_time);
                $periodEnd = Carbon::parse($period->end_time);

                // Check for overlap
                if ($requestedStart < $periodEnd && $requestedEnd > $periodStart) {
                    $conflicts[] = [
                        'schedule_id' => $schedule->id,
                        'schedule_name' => $schedule->name,
                        'type' => $schedule->type,
                        'conflicting_period' => [
                            'start' => $period->start_time,
                            'end' => $period->end_time,
                        ],
                    ];
                }
            }
        }

        return [
            'has_conflict' => !empty($conflicts),
            'conflicts' => $conflicts,
        ];
    }

    /**
     * Find alternative time slots when conflict exists.
     */
    public function findAlternativeSlots(
        User $technician,
        string $date,
        int $duration,
        int $maxResults = 5
    ): array {
        $alternatives = [];
        $currentDate = Carbon::parse($date);

        // Check up to 7 days forward
        for ($day = 0; $day < 7 && count($alternatives) < $maxResults; $day++) {
            $checkDate = $currentDate->copy()->addDays($day)->format('Y-m-d');

            $slots = $this->technicianService->getAvailableSlots(
                $technician,
                $checkDate,
                $duration,
                config('zap.conflict_detection.buffer_minutes', 15)
            );

            foreach ($slots as $slot) {
                $alternatives[] = array_merge($slot, ['date' => $checkDate]);

                if (count($alternatives) >= $maxResults) {
                    break;
                }
            }
        }

        return $alternatives;
    }

    /**
     * Schedule equipment preventive maintenance.
     */
    public function schedulePreventiveMaintenance(
        Equipment $equipment,
        string $maintenanceType,
        string $frequency,
        ?string $startDate = null
    ) {
        $startDate = $startDate ?? now()->format('Y-m-d');

        // Map frequency to days
        $intervalDays = match ($frequency) {
            'weekly' => 7,
            'biweekly' => 14,
            'monthly' => 30,
            'quarterly' => 90,
            'semi-annually' => 182,
            'annually' => 365,
            default => 30,
        };

        // Create maintenance schedule
        $schedule = Zap::for($equipment)
            ->named("PM - {$maintenanceType}")
            ->description("Preventive maintenance: {$maintenanceType} ({$frequency})")
            ->custom()
            ->from($startDate)
            ->to(Carbon::parse($startDate)->addYear()->format('Y-m-d'))
            ->addPeriod('09:00', '10:00') // Default 1-hour window
            ->withMetadata([
                'maintenance_type' => $maintenanceType,
                'frequency' => $frequency,
                'interval_days' => $intervalDays,
                'equipment_id' => $equipment->id,
                'automated' => true,
            ])
            ->save();

        // Update equipment
        $equipment->update([
            'next_maintenance_due_at' => Carbon::parse($startDate)->addDays($intervalDays),
        ]);

        return $schedule;
    }

    /**
     * Create emergency appointment with rule overrides.
     */
    public function scheduleEmergency(
        User $technician,
        string $date,
        string $startTime,
        string $endTime,
        array $metadata = []
    ) {
        // Emergency appointments bypass normal rules
        return Zap::for($technician)
            ->named($metadata['name'] ?? 'Emergency Service')
            ->description($metadata['description'] ?? 'Emergency service appointment')
            ->appointment()
            ->from($date)
            ->addPeriod($startTime, $endTime)
            ->withMetadata(array_merge($metadata, [
                'emergency' => true,
                'created_by' => auth()->id(),
                'created_at' => now()->toIso8601String(),
            ]))
            ->save();
    }

    /**
     * Get buffer time from configuration.
     */
    public function getBufferTime(): int
    {
        return config('zap.conflict_detection.buffer_minutes', 15);
    }

    /**
     * Check if a technician has any capacity remaining for a day.
     */
    public function getTechnicianDayCapacity(User $technician, string $date): array
    {
        $workingHours = config('zap.default_rules.working_hours', [
            'start' => '08:00',
            'end' => '18:00',
        ]);

        $totalMinutes = Carbon::parse($workingHours['start'])
            ->diffInMinutes(Carbon::parse($workingHours['end']));

        // Get booked minutes
        $bookedMinutes = 0;
        $appointments = $technician->schedules()
            ->where('type', 'appointment')
            ->where(function ($query) use ($date) {
                $query->whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date);
            })
            ->with('periods')
            ->get();

        foreach ($appointments as $appointment) {
            foreach ($appointment->periods as $period) {
                $bookedMinutes += Carbon::parse($period->start_time)
                    ->diffInMinutes(Carbon::parse($period->end_time));
            }
        }

        $availableMinutes = max(0, $totalMinutes - $bookedMinutes);

        return [
            'total_minutes' => $totalMinutes,
            'booked_minutes' => $bookedMinutes,
            'available_minutes' => $availableMinutes,
            'utilization_percent' => $totalMinutes > 0 ? round(($bookedMinutes / $totalMinutes) * 100) : 0,
        ];
    }
}
