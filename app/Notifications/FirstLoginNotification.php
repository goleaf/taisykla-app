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
            ->subject('Set up your account')
            ->greeting('Welcome!')
            ->line('Your account has been created.')
            ->line('Username: ' . $notifiable->email)
            ->line('Use the link below to set your password and access the system for the first time.')
            ->action('Set up password', $this->resetUrl)
            ->line('For security, your password must include uppercase and lowercase letters, a number, and a symbol.');
    }
}
