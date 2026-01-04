<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class FirstLoginNotification extends Notification
{
    use Queueable;

    public function __construct(private string $resetUrl)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Set up your Taisykla account')
            ->view('emails.onboarding', [
                'user' => $notifiable,
                'resetUrl' => $this->resetUrl,
            ]);
    }
}
