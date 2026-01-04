<?php

namespace App\Services\Scheduling;

use App\Models\Appointment;
use App\Models\RecurringSchedule;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecurringScheduleService
{
    /**
     * Create a recurring schedule
     */
    public function createRecurringSchedule(array $data): RecurringSchedule
    {
        $schedule = RecurringSchedule::create([
            'work_order_id' => $data['work_order_id'] ?? null,
            'assigned_to_user_id' => $data['assigned_to_user_id'],
            'starts_at' => Carbon::parse($data['starts_at']),
            'duration_minutes' => $data['duration_minutes'] ?? 60,
            'time_window' => $data['time_window'] ?? null,
            'frequency' => $data['frequency'], // daily, weekly, biweekly, monthly, custom
            'interval' => $data['interval'] ?? 1,
            'days_of_week' => $data['days_of_week'] ?? null, // For weekly: [1,2,3,4,5] for Mon-Fri
            'day_of_month' => $data['day_of_month'] ?? null, // For monthly: 1-31
            'occurrence_count' => $data['occurrence_count'] ?? null,
            'ends_at' => isset($data['ends_at']) ? Carbon::parse($data['ends_at']) : null,
            'next_run_at' => Carbon::parse($data['starts_at']),
            'is_active' => true,
            'notes' => $data['notes'] ?? null,
        ]);

        // Generate initial occurrences
        $this->generateOccurrences($schedule, 30); // Generate 30 days ahead

        return $schedule;
    }

    /**
     * Generate appointment occurrences for a recurring schedule
     */
    public function generateOccurrences(RecurringSchedule $schedule, int $daysAhead = 30): Collection
    {
        $appointments = collect();
        $currentDate = Carbon::parse($schedule->next_run_at);
        $endDate = now()->addDays($daysAhead);

        if ($schedule->ends_at && $schedule->ends_at < $endDate) {
            $endDate = $schedule->ends_at;
        }

        $occurrenceCount = 0;
        $maxOccurrences = $schedule->occurrence_count ?? 999;

        while ($currentDate <= $endDate && $occurrenceCount < $maxOccurrences) {
            if ($this->shouldCreateOccurrence($schedule, $currentDate)) {
                // Check if appointment already exists
                $exists = Appointment::where('recurring_schedule_id', $schedule->id)
                    ->whereDate('scheduled_start_at', $currentDate->toDateString())
                    ->exists();

                if (!$exists) {
                    $startTime = $currentDate->copy()->setTimeFromTimeString(
                        Carbon::parse($schedule->starts_at)->format('H:i:s')
                    );

                    $appointment = Appointment::create([
                        'work_order_id' => $schedule->work_order_id,
                        'assigned_to_user_id' => $schedule->assigned_to_user_id,
                        'recurring_schedule_id' => $schedule->id,
                        'scheduled_start_at' => $startTime,
                        'scheduled_end_at' => $startTime->copy()->addMinutes($schedule->duration_minutes),
                        'time_window' => $schedule->time_window,
                        'status' => 'scheduled',
                        'notes' => $schedule->notes,
                    ]);

                    $appointments->push($appointment);
                }

                $occurrenceCount++;
            }

            $currentDate = $this->getNextOccurrence($schedule, $currentDate);
        }

        // Update next_run_at
        $schedule->update(['next_run_at' => $currentDate]);

        return $appointments;
    }

    /**
     * Check if we should create an occurrence on this date
     */
    protected function shouldCreateOccurrence(RecurringSchedule $schedule, Carbon $date): bool
    {
        if (!$schedule->is_active) {
            return false;
        }

        switch ($schedule->frequency) {
            case 'daily':
                return true;

            case 'weekly':
            case 'biweekly':
                $daysOfWeek = $schedule->days_of_week ?? [Carbon::parse($schedule->starts_at)->dayOfWeekIso];
                return in_array($date->dayOfWeekIso, $daysOfWeek);

            case 'monthly':
                $dayOfMonth = $schedule->day_of_month ?? Carbon::parse($schedule->starts_at)->day;
                // Handle months with fewer days
                $targetDay = min($dayOfMonth, $date->daysInMonth);
                return $date->day === $targetDay;

            case 'custom':
                $daysOfWeek = $schedule->days_of_week ?? [];
                return in_array($date->dayOfWeekIso, $daysOfWeek);

            default:
                return true;
        }
    }

    /**
     * Get the next occurrence date
     */
    protected function getNextOccurrence(RecurringSchedule $schedule, Carbon $currentDate): Carbon
    {
        $interval = $schedule->interval ?? 1;

        return match ($schedule->frequency) {
            'daily' => $currentDate->addDays($interval),
            'weekly' => $currentDate->addWeeks($interval),
            'biweekly' => $currentDate->addWeeks(2),
            'monthly' => $currentDate->addMonths($interval),
            'custom' => $currentDate->addDays(1), // Check each day for custom rules
            default => $currentDate->addDays($interval),
        };
    }

    /**
     * Edit a single occurrence
     */
    public function editSingleOccurrence(Appointment $appointment, array $data): Appointment
    {
        // Detach from recurring series
        $appointment->update([
            'recurring_schedule_id' => null, // Break the link
            'scheduled_start_at' => isset($data['scheduled_start_at'])
                ? Carbon::parse($data['scheduled_start_at'])
                : $appointment->scheduled_start_at,
            'scheduled_end_at' => isset($data['scheduled_end_at'])
                ? Carbon::parse($data['scheduled_end_at'])
                : $appointment->scheduled_end_at,
            'assigned_to_user_id' => $data['assigned_to_user_id'] ?? $appointment->assigned_to_user_id,
            'notes' => $data['notes'] ?? $appointment->notes,
            'is_exception' => true,
        ]);

        return $appointment->fresh();
    }

    /**
     * Edit all occurrences from a specific date onward
     */
    public function editSeriesFromDate(RecurringSchedule $schedule, Carbon $fromDate, array $data): RecurringSchedule
    {
        // Update the recurring schedule
        $schedule->update([
            'assigned_to_user_id' => $data['assigned_to_user_id'] ?? $schedule->assigned_to_user_id,
            'duration_minutes' => $data['duration_minutes'] ?? $schedule->duration_minutes,
            'time_window' => $data['time_window'] ?? $schedule->time_window,
            'notes' => $data['notes'] ?? $schedule->notes,
        ]);

        // Update future appointments
        Appointment::where('recurring_schedule_id', $schedule->id)
            ->where('scheduled_start_at', '>=', $fromDate)
            ->where('status', 'scheduled')
            ->update([
                'assigned_to_user_id' => $data['assigned_to_user_id'] ?? $schedule->assigned_to_user_id,
                'notes' => $data['notes'] ?? $schedule->notes,
            ]);

        // If times changed, regenerate occurrences
        if (isset($data['starts_at']) || isset($data['duration_minutes'])) {
            // Delete future unstarted appointments
            Appointment::where('recurring_schedule_id', $schedule->id)
                ->where('scheduled_start_at', '>=', $fromDate)
                ->where('status', 'scheduled')
                ->delete();

            // Update schedule times
            if (isset($data['starts_at'])) {
                $schedule->update(['starts_at' => Carbon::parse($data['starts_at'])]);
            }

            // Regenerate
            $this->generateOccurrences($schedule);
        }

        return $schedule->fresh();
    }

    /**
     * Edit all occurrences (entire series)
     */
    public function editAllOccurrences(RecurringSchedule $schedule, array $data): RecurringSchedule
    {
        return $this->editSeriesFromDate($schedule, now(), $data);
    }

    /**
     * Skip a single occurrence
     */
    public function skipOccurrence(Appointment $appointment, ?string $reason = null): Appointment
    {
        $appointment->update([
            'status' => 'skipped',
            'notes' => $reason ? ($appointment->notes . "\n[Skipped: {$reason}]") : $appointment->notes,
        ]);

        return $appointment;
    }

    /**
     * Postpone an occurrence
     */
    public function postponeOccurrence(Appointment $appointment, Carbon $newDate): Appointment
    {
        // Create a new appointment at the new date
        $newAppointment = $appointment->replicate();
        $newAppointment->scheduled_start_at = $newDate;
        $newAppointment->scheduled_end_at = $newDate->copy()->addMinutes(
            $appointment->scheduled_start_at->diffInMinutes($appointment->scheduled_end_at)
        );
        $newAppointment->recurring_schedule_id = null; // Break recurring link
        $newAppointment->is_exception = true;
        $newAppointment->save();

        // Skip the original
        $this->skipOccurrence($appointment, 'Postponed to ' . $newDate->format('M j, Y'));

        return $newAppointment;
    }

    /**
     * End recurring schedule
     */
    public function endSchedule(RecurringSchedule $schedule, ?Carbon $endDate = null): RecurringSchedule
    {
        $endDate = $endDate ?? now();

        $schedule->update([
            'ends_at' => $endDate,
            'is_active' => false,
        ]);

        // Cancel future appointments
        Appointment::where('recurring_schedule_id', $schedule->id)
            ->where('scheduled_start_at', '>', $endDate)
            ->where('status', 'scheduled')
            ->update(['status' => 'canceled']);

        return $schedule->fresh();
    }

    /**
     * Delete recurring schedule and all occurrences
     */
    public function deleteSchedule(RecurringSchedule $schedule, bool $deleteAppointments = false): bool
    {
        return DB::transaction(function () use ($schedule, $deleteAppointments) {
            if ($deleteAppointments) {
                // Delete all appointments
                Appointment::where('recurring_schedule_id', $schedule->id)->delete();
            } else {
                // Just unlink appointments
                Appointment::where('recurring_schedule_id', $schedule->id)
                    ->update(['recurring_schedule_id' => null]);
            }

            return $schedule->delete();
        });
    }

    /**
     * Get upcoming occurrences for preview
     */
    public function previewOccurrences(array $scheduleData, int $count = 10): array
    {
        $occurrences = [];
        $startsAt = Carbon::parse($scheduleData['starts_at']);
        $currentDate = $startsAt->copy();
        $endDate = isset($scheduleData['ends_at']) ? Carbon::parse($scheduleData['ends_at']) : null;

        $tempSchedule = new RecurringSchedule($scheduleData + [
            'next_run_at' => $startsAt,
            'is_active' => true,
        ]);

        $generated = 0;
        $maxIterations = 365; // Safety limit
        $iterations = 0;

        while ($generated < $count && $iterations < $maxIterations) {
            if ($endDate && $currentDate > $endDate) {
                break;
            }

            if ($this->shouldCreateOccurrence($tempSchedule, $currentDate)) {
                $startTime = $currentDate->copy()->setTimeFromTimeString($startsAt->format('H:i:s'));

                $occurrences[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'formatted_date' => $currentDate->format('D, M j, Y'),
                    'start_time' => $startTime->format('g:i A'),
                    'end_time' => $startTime->copy()->addMinutes($scheduleData['duration_minutes'] ?? 60)->format('g:i A'),
                ];

                $generated++;
            }

            $currentDate = $this->getNextOccurrence($tempSchedule, $currentDate);
            $iterations++;
        }

        return $occurrences;
    }

    /**
     * Create preventive maintenance schedule from equipment
     */
    public function createPreventiveMaintenanceSchedule(int $equipmentId, array $data): RecurringSchedule
    {
        // Create a template work order for preventive maintenance
        $workOrder = WorkOrder::create([
            'equipment_id' => $equipmentId,
            'subject' => $data['subject'] ?? 'Preventive Maintenance',
            'description' => $data['description'] ?? 'Scheduled preventive maintenance',
            'priority' => 'standard',
            'status' => 'template', // Special status for templates
            'estimated_minutes' => $data['duration_minutes'] ?? 60,
        ]);

        return $this->createRecurringSchedule([
            'work_order_id' => $workOrder->id,
            'assigned_to_user_id' => $data['assigned_to_user_id'],
            'starts_at' => $data['starts_at'],
            'duration_minutes' => $data['duration_minutes'] ?? 60,
            'frequency' => $data['frequency'] ?? 'monthly',
            'interval' => $data['interval'] ?? 1,
            'notes' => 'Preventive maintenance for equipment #' . $equipmentId,
        ]);
    }
}
