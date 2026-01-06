<?php

namespace App\Services\Scheduling;

use App\Models\User;
use Illuminate\Support\Collection;
use Zap\Facades\Zap;

/**
 * Service for managing technician schedules using Laravel Zap.
 */
class TechnicianScheduleService
{
    /**
     * Set working hours for a technician.
     *
     * @param User $technician
     * @param array $hours ['start' => '08:00', 'end' => '17:00']
     * @param array $weekdays ['monday', 'tuesday', ...]
     * @param string|null $name
     * @return \Zap\Models\Schedule
     */
    public function setWorkingHours(
        User $technician,
        array $hours,
        array $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        ?string $name = null
    ) {
        $name = $name ?? "{$technician->name} - Working Hours";

        return Zap::for($technician)
            ->named($name)
            ->description('Regular working hours')
            ->availability()
            ->forYear(now()->year)
            ->addPeriod($hours['start'], $hours['end'])
            ->weekly($weekdays)
            ->save();
    }

    /**
     * Block time for a technician (vacation, time off, etc).
     *
     * @param User $technician
     * @param \Carbon\Carbon $from
     * @param \Carbon\Carbon $to
     * @param string|null $reason
     * @return \Zap\Models\Schedule
     */
    public function blockTime(
        User $technician,
        \Carbon\Carbon $from,
        \Carbon\Carbon $to,
        ?string $reason = null
    ) {
        $name = $reason ?? 'Time Off';

        return Zap::for($technician)
            ->named($name)
            ->blocked()
            ->from($from->format('Y-m-d'))
            ->to($to->format('Y-m-d'))
            ->addPeriod('00:00', '23:59')
            ->save();
    }

    /**
     * Get available time slots for a technician on a specific date.
     *
     * @param User $technician
     * @param string $date
     * @param int $duration Minutes
     * @param int $bufferTime Minutes between appointments
     * @return array
     */
    public function getAvailableSlots(
        User $technician,
        string $date,
        int $duration = 60,
        int $bufferTime = 15
    ): array {
        return $technician->getBookableSlots(
            date: $date,
            duration: $duration,
            bufferTime: $bufferTime
        );
    }

    /**
     * Get the next available slot for a technician.
     *
     * @param User $technician
     * @param string $startDate
     * @param int $duration
     * @param int $bufferTime
     * @return array|null
     */
    public function getNextAvailableSlot(
        User $technician,
        string $startDate,
        int $duration = 60,
        int $bufferTime = 15
    ): ?array {
        return $technician->getNextBookableSlot(
            startDate: $startDate,
            duration: $duration,
            bufferTime: $bufferTime
        );
    }

    /**
     * Check if technician is available at a specific time.
     *
     * @param User $technician
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @return bool
     */
    public function isAvailable(
        User $technician,
        string $date,
        string $startTime,
        string $endTime
    ): bool {
        return $technician->isAvailableAt(
            date: $date,
            startTime: $startTime,
            endTime: $endTime
        );
    }

    /**
     * Find technicians available for a time slot.
     *
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param Collection|null $technicians Filter to specific technicians
     * @return Collection
     */
    public function findAvailableTechnicians(
        string $date,
        string $startTime,
        string $endTime,
        ?Collection $technicians = null
    ): Collection {
        $technicians = $technicians ?? User::whereHas('roles', function ($q) {
            $q->where('name', 'technician');
        })->where('is_active', true)->get();

        return $technicians->filter(function (User $technician) use ($date, $startTime, $endTime) {
            return $this->isAvailable($technician, $date, $startTime, $endTime);
        });
    }

    /**
     * Book an appointment for a technician.
     *
     * @param User $technician
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param array $metadata Additional data to store with appointment
     * @param string|null $name
     * @return \Zap\Models\Schedule
     */
    public function bookAppointment(
        User $technician,
        string $date,
        string $startTime,
        string $endTime,
        array $metadata = [],
        ?string $name = null
    ) {
        $name = $name ?? 'Service Appointment';

        return Zap::for($technician)
            ->named($name)
            ->appointment()
            ->from($date)
            ->addPeriod($startTime, $endTime)
            ->withMetadata($metadata)
            ->save();
    }

    /**
     * Get all appointments for a technician within a date range.
     *
     * @param User $technician
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getAppointments(
        User $technician,
        string $startDate,
        string $endDate
    ): Collection {
        return $technician->schedules()
            ->where('type', 'appointment')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->with('periods')
            ->orderBy('start_date')
            ->get();
    }

    /**
     * Cancel an appointment by deleting the schedule.
     *
     * @param int $scheduleId
     * @return bool
     */
    public function cancelAppointment(int $scheduleId): bool
    {
        return \Zap\Models\Schedule::destroy($scheduleId) > 0;
    }
}
