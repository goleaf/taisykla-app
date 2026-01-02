<?php

use App\Models\User;
use App\Services\MfaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $code = '';
    public ?string $method = null;
    public ?string $destination = null;

    public function mount(): void
    {
        $user = $this->challengeUser();
        if (! $user) {
            $this->redirectRoute('login', navigate: true);
            return;
        }

        $this->method = $user->mfa_method ?? 'email';
        $this->destination = $this->maskDestination($user);
    }

    public function verify(): void
    {
        $rules = [
            'code' => ['required', 'string'],
        ];
        if (in_array($this->method, ['auth_app', 'email'], true)) {
            $rules['code'][] = 'digits:6';
        }
        $this->validate($rules);

        $user = $this->challengeUser();
        if (! $user) {
            $this->redirectRoute('login', navigate: true);
            return;
        }

        $mfa = app(MfaService::class);
        $valid = $this->method === 'auth_app'
            ? $mfa->verifyTotp($user, $this->code)
            : $mfa->verifyEmailCode($user, $this->code);

        if (! $valid) {
            $this->addError('code', 'Invalid or expired verification code.');
            return;
        }

        Auth::login($user, (bool) session('mfa_remember', false));
        session()->forget(['mfa_user_id', 'mfa_remember']);
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    public function resend(): void
    {
        $user = $this->challengeUser();
        if (! $user) {
            $this->redirectRoute('login', navigate: true);
            return;
        }

        if ($this->method === 'email') {
            app(MfaService::class)->sendEmailCode($user);
            Session::flash('status', 'Verification code resent.');
        }
    }

    private function challengeUser(): ?User
    {
        $userId = session('mfa_user_id');
        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }

    private function maskDestination(User $user): string
    {
        if ($user->mfa_method === 'auth_app') {
            return 'Authenticator app';
        }

        $email = $user->mfa_email ?: $user->email;
        $parts = explode('@', $email, 2);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];
        $masked = strlen($name) > 2
            ? substr($name, 0, 1) . str_repeat('*', max(strlen($name) - 2, 1)) . substr($name, -1)
            : str_repeat('*', strlen($name));

        return $masked . '@' . $domain;
    }
}; ?>

<div>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-4 text-sm text-gray-600">
        @if ($method === 'auth_app')
            Enter the 6-digit code from your authenticator app.
        @else
            Enter the verification code sent to {{ $destination }}.
        @endif
    </div>

    <form wire:submit="verify">
        <div>
            <x-input-label for="code" :value="__('Verification Code')" />
            <x-text-input wire:model="code" id="code" class="block mt-1 w-full" type="text" name="code" required autofocus autocomplete="one-time-code" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            @if ($method === 'email')
                <button type="button" class="text-sm text-gray-600 underline" wire:click="resend">Resend code</button>
            @endif
            <x-primary-button>
                {{ __('Verify') }}
            </x-primary-button>
        </div>
    </form>
</div>
