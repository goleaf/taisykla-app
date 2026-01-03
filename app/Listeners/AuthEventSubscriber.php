<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Events\Dispatcher;

class AuthEventSubscriber
{
    public function handleLogin(Login $event): void
    {
        if ($event->user instanceof User) {
            $event->user->forceFill([
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ])->save();
        }

        app(AuditLogger::class)->log(
            'auth.login',
            $event->user instanceof User ? $event->user : null,
            'User signed in.',
            ['remember' => (bool) $event->remember]
        );
    }

    public function handleLogout(Logout $event): void
    {
        app(AuditLogger::class)->log(
            'auth.logout',
            $event->user instanceof User ? $event->user : null,
            'User signed out.'
        );
    }

    public function handleRegistered(Registered $event): void
    {
        app(AuditLogger::class)->log(
            'auth.registered',
            $event->user instanceof User ? $event->user : null,
            'User registered.'
        );
    }

    public function handlePasswordResetLinkSent(PasswordResetLinkSent $event): void
    {
        app(AuditLogger::class)->log(
            'auth.password_reset_requested',
            $event->user instanceof User ? $event->user : null,
            'Password reset link sent.'
        );
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        app(AuditLogger::class)->log(
            'auth.password_reset',
            $event->user instanceof User ? $event->user : null,
            'Password reset completed.'
        );
    }

    public function handleVerified(Verified $event): void
    {
        app(AuditLogger::class)->log(
            'auth.email_verified',
            $event->user instanceof User ? $event->user : null,
            'Email address verified.'
        );
    }

    public function handleLockout(Lockout $event): void
    {
        $email = (string) $event->request->input('email');
        $user = $email !== '' ? User::where('email', $email)->first() : null;

        app(AuditLogger::class)->log(
            'auth.throttled',
            $user,
            'Login throttled due to too many attempts.',
            ['email' => $email]
        );
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(Login::class, [self::class, 'handleLogin']);
        $events->listen(Logout::class, [self::class, 'handleLogout']);
        $events->listen(Registered::class, [self::class, 'handleRegistered']);
        $events->listen(PasswordResetLinkSent::class, [self::class, 'handlePasswordResetLinkSent']);
        $events->listen(PasswordReset::class, [self::class, 'handlePasswordReset']);
        $events->listen(Verified::class, [self::class, 'handleVerified']);
        $events->listen(Lockout::class, [self::class, 'handleLockout']);
    }
}
