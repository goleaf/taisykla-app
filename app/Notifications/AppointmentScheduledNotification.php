<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Zap\Models\Schedule;

class AppointmentScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Schedule $schedule,
        public ServiceRequest $serviceRequest,
        public User $technician,
        public bool $isTechnicianNotification = false
    ) {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $period = $this->schedule->periods->first();
        $scheduledDate = \Carbon\Carbon::parse($this->schedule->start_date);
        $startTime = $period?->start_time ?? 'TBD';
        $endTime = $period?->end_time ?? 'TBD';

        if ($this->isTechnicianNotification) {
            return (new MailMessage)
                ->subject('New Appointment Assigned - SR #' . str_pad($this->serviceRequest->id, 5, '0', STR_PAD_LEFT))
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('A new service appointment has been scheduled for you.')
                ->line('**Service Request:** #' . str_pad($this->serviceRequest->id, 5, '0', STR_PAD_LEFT))
                ->line('**Customer:** ' . ($this->serviceRequest->customer->name ?? 'N/A'))
                ->line('**Date:** ' . $scheduledDate->format('l, F j, Y'))
                ->line('**Time:** ' . $startTime . ' - ' . $endTime)
                ->line('**Equipment:** ' . ($this->serviceRequest->equipment->model ?? 'N/A'))
                ->action('View Service Request', url(route('service-requests.show', $this->serviceRequest)))
                ->line('Please ensure you are available at the scheduled time.');
        }

        return (new MailMessage)
            ->subject('Appointment Confirmed - SR #' . str_pad($this->serviceRequest->id, 5, '0', STR_PAD_LEFT))
            ->greeting('Hello,')
            ->line('Your service appointment has been confirmed.')
            ->line('**Service Request:** #' . str_pad($this->serviceRequest->id, 5, '0', STR_PAD_LEFT))
            ->line('**Technician:** ' . $this->technician->name)
            ->line('**Date:** ' . $scheduledDate->format('l, F j, Y'))
            ->line('**Time:** ' . $startTime . ' - ' . $endTime)
            ->action('View Appointment', url(route('service-requests.show', $this->serviceRequest)))
            ->line('We will notify you when the technician is on their way.')
            ->line('Thank you for choosing our service!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $period = $this->schedule->periods->first();

        return [
            'type' => 'appointment_scheduled',
            'schedule_id' => $this->schedule->id,
            'service_request_id' => $this->serviceRequest->id,
            'technician_id' => $this->technician->id,
            'technician_name' => $this->technician->name,
            'scheduled_date' => $this->schedule->start_date,
            'start_time' => $period?->start_time,
            'end_time' => $period?->end_time,
            'message' => $this->isTechnicianNotification
                ? 'New appointment assigned for ' . \Carbon\Carbon::parse($this->schedule->start_date)->format('M j')
                : 'Appointment confirmed for ' . \Carbon\Carbon::parse($this->schedule->start_date)->format('M j'),
        ];
    }
}
