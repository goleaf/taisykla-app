<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ServiceRequest $serviceRequest,
        public ?User $technician = null,
        public bool $isTechnicianNotification = false
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $requestId = str_pad($this->serviceRequest->id, 5, '0', STR_PAD_LEFT);

        if ($this->isTechnicianNotification) {
            return (new MailMessage)
                ->subject('Appointment Cancelled - SR #' . $requestId)
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('An appointment has been cancelled.')
                ->line('**Service Request:** #' . $requestId)
                ->line('**Customer:** ' . ($this->serviceRequest->customer->name ?? 'N/A'))
                ->line('This time slot is now available for other appointments.')
                ->action('View Schedule', url(route('schedule.index')));
        }

        return (new MailMessage)
            ->subject('Appointment Cancelled - SR #' . $requestId)
            ->greeting('Hello,')
            ->line('Your service appointment has been cancelled.')
            ->line('**Service Request:** #' . $requestId)
            ->line('Please contact us to reschedule at your earliest convenience.')
            ->action('Contact Us', url('/'))
            ->line('We apologize for any inconvenience.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'appointment_cancelled',
            'service_request_id' => $this->serviceRequest->id,
            'technician_name' => $this->technician?->name,
            'message' => 'Appointment cancelled for SR #' . str_pad($this->serviceRequest->id, 5, '0', STR_PAD_LEFT),
        ];
    }
}
