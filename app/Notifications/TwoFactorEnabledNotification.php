<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorEnabledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Two-Factor Authentication Enabled')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Two-factor authentication has been enabled on your account.')
            ->line('If you did not make this change, please contact support immediately.')
            ->action('View Security Settings', url(route('profile')))
            ->line('Keep your backup codes in a safe place.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'security',
            'message' => 'Two-factor authentication has been enabled on your account.',
        ];
    }
}
