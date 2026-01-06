<?php

namespace App\Listeners;

use App\Models\ServiceRequest;
use App\Notifications\AppointmentCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Zap\Events\ScheduleDeleted;

class SendCancellationNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ScheduleDeleted $event): void
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
            $serviceRequest = ServiceRequest::with(['customer', 'technician'])
                ->find($metadata['service_request_id']);

            if (!$serviceRequest) {
                return;
            }

            // Get the technician from the schedulable model
            $technician = $schedule->schedulable;

            // Notify customer
            if ($serviceRequest->customer && method_exists($serviceRequest->customer, 'notify')) {
                $serviceRequest->customer->notify(
                    new AppointmentCancelledNotification($serviceRequest, $technician)
                );
            }

            // Notify technician
            if ($technician && method_exists($technician, 'notify')) {
                $technician->notify(
                    new AppointmentCancelledNotification($serviceRequest, $technician, true)
                );
            }

            Log::info('Cancellation notifications sent', [
                'service_request_id' => $serviceRequest->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send cancellation notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
