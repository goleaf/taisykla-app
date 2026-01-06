<?php

namespace App\Listeners;

use App\Models\ServiceRequest;
use App\Notifications\AppointmentScheduledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Zap\Events\ScheduleCreated;

class SendScheduleConfirmation implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ScheduleCreated $event): void
    {
        $schedule = $event->schedule;

        // Only process appointment type schedules
        if ($schedule->type !== 'appointment') {
            return;
        }

        $metadata = $schedule->metadata ?? [];

        // Check if this is a service request appointment
        if (!isset($metadata['service_request_id'])) {
            return;
        }

        try {
            $serviceRequest = ServiceRequest::with(['customer', 'technician', 'equipment'])
                ->find($metadata['service_request_id']);

            if (!$serviceRequest) {
                Log::warning('Service request not found for schedule confirmation', [
                    'schedule_id' => $schedule->id,
                    'service_request_id' => $metadata['service_request_id'],
                ]);
                return;
            }

            // Get the technician from the schedulable model
            $technician = $schedule->schedulable;

            // Notify customer
            if ($serviceRequest->customer && method_exists($serviceRequest->customer, 'notify')) {
                $serviceRequest->customer->notify(
                    new AppointmentScheduledNotification($schedule, $serviceRequest, $technician)
                );
            }

            // Notify technician
            if ($technician && method_exists($technician, 'notify')) {
                $technician->notify(
                    new AppointmentScheduledNotification($schedule, $serviceRequest, $technician, true)
                );
            }

            Log::info('Schedule confirmation notifications sent', [
                'schedule_id' => $schedule->id,
                'service_request_id' => $serviceRequest->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send schedule confirmation', [
                'schedule_id' => $schedule->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
