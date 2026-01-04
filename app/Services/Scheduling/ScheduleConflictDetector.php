<?php

namespace App\Services\Scheduling;

use App\Models\Appointment;
use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ScheduleConflictDetector
{
    /**
     * Detect all conflicts for a potential assignment
     */
    public function detectConflicts(
        User $technician,
        Carbon $startTime,
        Carbon $endTime,
        ?int $excludeWorkOrderId = null
    ): Collection {
        $conflicts = collect();

        // Check overlapping appointments
        $overlapping = $this->findOverlappingAppointments($technician, $startTime, $endTime, $excludeWorkOrderId);
        foreach ($overlapping as $appointment) {
            $conflicts->push([
                'type' => 'overlap',
                'severity' => 'critical',
                'message' => "Overlaps with existing appointment for WO #{$appointment->work_order_id}",
                'appointment' => $appointment,
                'work_order' => $appointment->workOrder,
            ]);
        }

        // Check insufficient travel time
        $travelConflict = $this->checkTravelTime($technician, $startTime, $excludeWorkOrderId);
        if ($travelConflict) {
            $conflicts->push($travelConflict);
        }

        // Check customer time preferences
        $timeWindowConflict = $this->checkTimeWindowViolation($startTime, $endTime);
        if ($timeWindowConflict) {
            $conflicts->push($timeWindowConflict);
        }

        // Check overtime/max hours
        $overtimeConflicts = $this->checkOvertimeViolations($technician, $startTime, $endTime);
        $conflicts = $conflicts->merge($overtimeConflicts);

        return $conflicts;
    }

    /**
     * Find overlapping appointments
     */
    public function findOverlappingAppointments(
        User $technician,
        Carbon $startTime,
        Carbon $endTime,
        ?int $excludeWorkOrderId = null
    ): Collection {
        return Appointment::where('assigned_to_user_id', $technician->id)
            ->when($excludeWorkOrderId, fn($q) => $q->where('work_order_id', '!=', $excludeWorkOrderId))
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    // New appointment starts during existing
                    $q->where('scheduled_start_at', '<=', $startTime)
                        ->where('scheduled_end_at', '>', $startTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // New appointment ends during existing
                    $q->where('scheduled_start_at', '<', $endTime)
                        ->where('scheduled_end_at', '>=', $endTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // New appointment contains existing
                    $q->where('scheduled_start_at', '>=', $startTime)
                        ->where('scheduled_end_at', '<=', $endTime);
                });
            })
            ->with('workOrder')
            ->get();
    }

    /**
     * Check if there's sufficient travel time from previous appointment
     */
    private function checkTravelTime(
        User $technician,
        Carbon $startTime,
        ?int $excludeWorkOrderId = null
    ): ?array {
        // Find the previous appointment
        $previousAppointment = Appointment::where('assigned_to_user_id', $technician->id)
            ->when($excludeWorkOrderId, fn($q) => $q->where('work_order_id', '!=', $excludeWorkOrderId))
            ->where('scheduled_end_at', '<=', $startTime)
            ->orderBy('scheduled_end_at', 'desc')
            ->with('workOrder')
            ->first();

        if (!$previousAppointment || !$previousAppointment->workOrder) {
            return null;
        }

        $prevWO = $previousAppointment->workOrder;
        if (!$prevWO->location_latitude || !$prevWO->location_longitude) {
            return null;
        }

        // Calculate gap between appointments
        $gapMinutes = $previousAppointment->scheduled_end_at->diffInMinutes($startTime);

        // Estimate minimum travel time (simplified)
        $estimatedTravelMinutes = 15; // minimum 15 minutes between jobs

        if ($gapMinutes < $estimatedTravelMinutes) {
            return [
                'type' => 'insufficient_travel',
                'severity' => 'warning',
                'message' => "Only {$gapMinutes} min gap after previous job. Recommend {$estimatedTravelMinutes}+ min.",
                'previous_appointment' => $previousAppointment,
                'gap_minutes' => $gapMinutes,
                'recommended_gap' => $estimatedTravelMinutes,
            ];
        }

        return null;
    }

    /**
     * Check if time falls outside preferred time windows
     */
    private function checkTimeWindowViolation(Carbon $startTime, Carbon $endTime): ?array
    {
        // Business hours check (8 AM - 6 PM by default)
        $businessStart = 8;
        $businessEnd = 18;

        $hourStart = (int) $startTime->format('H');
        $hourEnd = (int) $endTime->format('H');

        if ($hourStart < $businessStart || $hourEnd > $businessEnd) {
            return [
                'type' => 'outside_business_hours',
                'severity' => 'info',
                'message' => 'Scheduled outside standard business hours (8AM-6PM)',
                'scheduled_start' => $startTime->format('g:i A'),
                'scheduled_end' => $endTime->format('g:i A'),
            ];
        }

        return null;
    }

    /**
     * Check for overtime violations
     */
    private function checkOvertimeViolations(User $technician, Carbon $startTime, Carbon $endTime): Collection
    {
        $conflicts = collect();

        $jobDurationMinutes = $startTime->diffInMinutes($endTime);

        // Calculate daily totals
        $dailyMinutes = Appointment::where('assigned_to_user_id', $technician->id)
            ->whereDate('scheduled_start_at', $startTime->toDateString())
            ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, scheduled_start_at, scheduled_end_at)'));

        $dailyTotalWithNew = $dailyMinutes + $jobDurationMinutes;
        $maxDailyMinutes = $technician->max_daily_minutes ?? 480; // Default 8 hours

        if ($dailyTotalWithNew > $maxDailyMinutes) {
            $overtime = $dailyTotalWithNew - $maxDailyMinutes;
            $conflicts->push([
                'type' => 'daily_overtime',
                'severity' => $technician->overtime_allowed ? 'warning' : 'critical',
                'message' => "Exceeds daily limit by " . floor($overtime / 60) . "h " . ($overtime % 60) . "m",
                'current_minutes' => $dailyMinutes,
                'new_total' => $dailyTotalWithNew,
                'limit' => $maxDailyMinutes,
                'overtime_minutes' => $overtime,
                'overtime_allowed' => $technician->overtime_allowed,
            ]);
        }

        // Calculate weekly totals
        $weekStart = $startTime->copy()->startOfWeek();
        $weekEnd = $startTime->copy()->endOfWeek();

        $weeklyMinutes = Appointment::where('assigned_to_user_id', $technician->id)
            ->whereBetween('scheduled_start_at', [$weekStart, $weekEnd])
            ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, scheduled_start_at, scheduled_end_at)'));

        $weeklyTotalWithNew = $weeklyMinutes + $jobDurationMinutes;
        $maxWeeklyMinutes = $technician->max_weekly_minutes ?? 2400; // Default 40 hours

        if ($weeklyTotalWithNew > $maxWeeklyMinutes) {
            $overtime = $weeklyTotalWithNew - $maxWeeklyMinutes;
            $conflicts->push([
                'type' => 'weekly_overtime',
                'severity' => $technician->overtime_allowed ? 'warning' : 'critical',
                'message' => "Exceeds weekly limit by " . floor($overtime / 60) . " hours",
                'current_minutes' => $weeklyMinutes,
                'new_total' => $weeklyTotalWithNew,
                'limit' => $maxWeeklyMinutes,
                'overtime_minutes' => $overtime,
                'overtime_allowed' => $technician->overtime_allowed,
            ]);
        }

        return $conflicts;
    }

    /**
     * Validate assignment with real-time conflict checking
     */
    public function validateAssignment(
        WorkOrder $workOrder,
        User $technician,
        ?Carbon $startTime = null,
        ?int $estimatedMinutes = null
    ): array {
        $startTime = $startTime ?? $workOrder->scheduled_start_at ?? now();
        $estimatedMinutes = $estimatedMinutes ?? $workOrder->estimated_minutes ?? 60;
        $endTime = $startTime->copy()->addMinutes($estimatedMinutes);

        $conflicts = $this->detectConflicts($technician, $startTime, $endTime, $workOrder->id);

        $hasCritical = $conflicts->where('severity', 'critical')->isNotEmpty();
        $hasWarning = $conflicts->where('severity', 'warning')->isNotEmpty();

        return [
            'valid' => !$hasCritical,
            'conflicts' => $conflicts,
            'has_critical' => $hasCritical,
            'has_warning' => $hasWarning,
            'can_proceed' => !$hasCritical,
            'requires_confirmation' => $hasWarning && !$hasCritical,
        ];
    }

    /**
     * Suggest alternative times when conflicts are found
     */
    public function suggestAlternatives(
        User $technician,
        Carbon $preferredStart,
        int $durationMinutes,
        int $suggestions = 3
    ): Collection {
        $alternatives = collect();
        $searchDate = $preferredStart->copy()->startOfDay();
        $endOfSearch = $searchDate->copy()->addDays(5);

        while ($alternatives->count() < $suggestions && $searchDate < $endOfSearch) {
            $slots = $this->findAvailableSlots($technician, $searchDate, $durationMinutes);

            foreach ($slots as $slot) {
                if ($alternatives->count() >= $suggestions) {
                    break;
                }

                // Skip if it's the same as the preferred time
                if ($slot['start']->equalTo($preferredStart)) {
                    continue;
                }

                $alternatives->push([
                    'start' => $slot['start'],
                    'end' => $slot['end'],
                    'date' => $slot['start']->format('D, M j'),
                    'time' => $slot['start']->format('g:i A') . ' - ' . $slot['end']->format('g:i A'),
                    'gap_from_preferred' => $preferredStart->diffForHumans($slot['start']),
                ]);
            }

            $searchDate->addDay();
        }

        return $alternatives;
    }

    /**
     * Find available time slots for a technician
     */
    public function findAvailableSlots(User $technician, Carbon $date, int $durationMinutes): array
    {
        $slots = [];
        $workdayStart = $date->copy()->setTime(8, 0);
        $workdayEnd = $date->copy()->setTime(18, 0);

        // Get all appointments for the day
        $appointments = Appointment::where('assigned_to_user_id', $technician->id)
            ->whereDate('scheduled_start_at', $date)
            ->orderBy('scheduled_start_at')
            ->get();

        $currentTime = $workdayStart->copy();

        foreach ($appointments as $appointment) {
            $appointmentStart = Carbon::parse($appointment->scheduled_start_at);
            $appointmentEnd = Carbon::parse($appointment->scheduled_end_at);

            // Gap before this appointment
            $gapMinutes = $currentTime->diffInMinutes($appointmentStart);
            if ($gapMinutes >= $durationMinutes + 15) { // Add 15 min buffer
                $slots[] = [
                    'start' => $currentTime->copy(),
                    'end' => $currentTime->copy()->addMinutes($durationMinutes),
                ];
            }

            $currentTime = $appointmentEnd->copy()->addMinutes(15); // 15 min buffer after
        }

        // Check remaining time after last appointment
        if ($currentTime < $workdayEnd) {
            $remainingMinutes = $currentTime->diffInMinutes($workdayEnd);
            if ($remainingMinutes >= $durationMinutes) {
                $slots[] = [
                    'start' => $currentTime->copy(),
                    'end' => $currentTime->copy()->addMinutes($durationMinutes),
                ];
            }
        }

        return $slots;
    }
}
