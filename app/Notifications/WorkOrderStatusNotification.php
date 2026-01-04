<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WorkOrderStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public WorkOrder $workOrder)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Work Order #' . $this->workOrder->id . ' Status Update')
            ->view('emails.work-order-status', [
                'workOrder' => $this->workOrder,
            ]);
    }
}
