<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;

class MfaCodeNotification extends Notification
{
    use Queueable;

    public function __construct(private string $code, private Carbon $expiresAt)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your verification code')
            ->line('Use this verification code to finish signing in:')
            ->line($this->code)
            ->line('This code expires ' . $this->expiresAt->diffForHumans() . '.');
    }
}
