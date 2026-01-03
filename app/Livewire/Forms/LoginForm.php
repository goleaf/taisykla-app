<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): User
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('email', $this->email)->first();
        if ($user) {
            $this->clearLockoutIfExpired($user);
            $this->ensureAccountNotLocked($user);
        }

        $credentials = $this->only(['email', 'password']);
        if (! Auth::validate($credentials)) {
            RateLimiter::hit($this->throttleKey());

            if ($user) {
                $this->recordFailedAttempt($user);
                $this->ensureAccountNotLocked($user);
            }

            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        $user ??= User::where('email', $this->email)->first();
        if (! $user) {
            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);
        }

        return $user;
    }

    public function login(User $user): void
    {
        Auth::login($user, $this->remember);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }

    protected function clearLockoutIfExpired(User $user): void
    {
        if (! $user->locked_until || $user->locked_until->isFuture()) {
            return;
        }

        $user->forceFill([
            'locked_until' => null,
            'failed_login_attempts' => 0,
        ])->save();
    }

    protected function ensureAccountNotLocked(User $user): void
    {
        if (! $user->locked_until || $user->locked_until->isPast()) {
            return;
        }

        $seconds = max(1, now()->diffInSeconds($user->locked_until));

        throw ValidationException::withMessages([
            'form.email' => trans('auth.locked', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function recordFailedAttempt(User $user): void
    {
        $attempts = $user->failed_login_attempts + 1;
        $lockoutAttempts = max((int) config('security.lockout.attempts', 5), 1);
        $lockoutMinutes = max((int) config('security.lockout.minutes', 15), 1);

        $updates = ['failed_login_attempts' => $attempts];
        if ($attempts >= $lockoutAttempts) {
            $updates['locked_until'] = now()->addMinutes($lockoutMinutes);
        }

        $user->forceFill($updates)->save();
    }
}
