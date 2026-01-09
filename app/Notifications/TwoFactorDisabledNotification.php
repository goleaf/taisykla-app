<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorDisabledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Two-Factor Authentication Disabled')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Two-factor authentication has been disabled on your account.')
            ->line('If you did not make this change, please secure your account immediately.')
            ->action('Secure Your Account', url(route('profile')))
            ->line('We recommend enabling 2FA for enhanced security.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'security',
            'message' => 'Two-factor authentication has been disabled on your account.',
        ];
    }
}
