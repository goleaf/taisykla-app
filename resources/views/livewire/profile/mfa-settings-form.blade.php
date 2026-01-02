<?php

use App\Services\MfaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $enabled = false;
    public string $method = 'email';
    public string $email = '';
    public string $phone = '';
    public string $secret = '';
    public string $authCode = '';
    public ?string $otpUrl = null;
    public bool $configured = false;

    public function mount(): void
    {
        $user = Auth::user();
        $this->enabled = (bool) $user->mfa_enabled;
        $this->method = in_array($user->mfa_method, ['email', 'auth_app'], true) ? $user->mfa_method : 'email';
        $this->email = $user->mfa_email ?: $user->email;
        $this->phone = $user->mfa_phone ?: ($user->phone ?? '');
        $this->configured = $user->mfa_enabled && $user->mfa_method === 'auth_app' && (bool) $user->mfa_secret;
    }

    public function updatedMethod(): void
    {
        $this->resetValidation();
        $this->authCode = '';
        if ($this->method !== 'auth_app') {
            $this->secret = '';
            $this->otpUrl = null;
        }
    }

    public function generateSecret(): void
    {
        $this->secret = app(MfaService::class)->generateSecret();
        $this->otpUrl = app(MfaService::class)->otpauthUrl(Auth::user(), $this->secret);
        $this->authCode = '';
    }

    public function save(): void
    {
        $user = Auth::user();
        $needsAuthSetup = $this->enabled
            && $this->method === 'auth_app'
            && (! $user->mfa_enabled || $user->mfa_method !== 'auth_app' || $this->secret !== '');

        $rules = [
            'enabled' => ['boolean'],
            'method' => ['required', Rule::in(['email', 'auth_app'])],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ];

        if ($this->enabled && $this->method === 'email') {
            $rules['email'][] = 'required';
        }

        if ($needsAuthSetup) {
            $rules['secret'] = ['required', 'string'];
            $rules['authCode'] = ['required', 'digits:6'];
        }

        $this->validate($rules);

        if (! $this->enabled) {
            $user->update([
                'mfa_enabled' => false,
                'mfa_method' => null,
                'mfa_phone' => null,
                'mfa_email' => null,
                'mfa_secret' => null,
                'mfa_confirmed_at' => null,
            ]);

            $this->configured = false;
            $this->dispatch('mfa-updated');
            return;
        }

        if ($this->method === 'email') {
            $user->update([
                'mfa_enabled' => true,
                'mfa_method' => 'email',
                'mfa_email' => $this->email,
                'mfa_phone' => null,
                'mfa_secret' => null,
                'mfa_confirmed_at' => now(),
            ]);

            $this->configured = false;
            $this->dispatch('mfa-updated');
            return;
        }

        if ($needsAuthSetup) {
            $mfa = app(MfaService::class);
            if (! $mfa->verifyTotpSecret($this->secret, $this->authCode)) {
                $this->addError('authCode', 'Invalid authenticator code.');
                return;
            }

            $user->update([
                'mfa_enabled' => true,
                'mfa_method' => 'auth_app',
                'mfa_email' => null,
                'mfa_phone' => null,
                'mfa_secret' => $this->secret,
                'mfa_confirmed_at' => now(),
            ]);

            $this->secret = '';
            $this->authCode = '';
            $this->otpUrl = null;
            $this->configured = true;
        } else {
            $user->update([
                'mfa_enabled' => true,
                'mfa_method' => 'auth_app',
            ]);
            $this->configured = true;
        }

        $this->dispatch('mfa-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Multi-Factor Authentication') }}</h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Add an extra verification step when signing in.') }}
        </p>
    </header>

    <form wire:submit="save" class="mt-6 space-y-6">
        <div class="flex items-center gap-3">
            <input wire:model="enabled" id="mfa_enabled" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
            <label for="mfa_enabled" class="text-sm text-gray-700">Enable MFA</label>
        </div>

        <div>
            <x-input-label for="mfa_method" :value="__('Verification Method')" />
            <select wire:model="method" id="mfa_method" class="mt-1 block w-full rounded-md border-gray-300">
                <option value="email">Email</option>
                <option value="auth_app">Authenticator app</option>
                <option value="sms" disabled>SMS (requires integration)</option>
                <option value="security_key" disabled>Security key (requires integration)</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('method')" />
        </div>

        @if ($method === 'email')
            <div>
                <x-input-label for="mfa_email" :value="__('Email for verification codes')" />
                <x-text-input wire:model="email" id="mfa_email" name="mfa_email" type="email" class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>
        @endif

        @if ($method === 'auth_app')
            <div class="rounded-md bg-gray-50 p-4 text-sm text-gray-700 space-y-2">
                <p>Generate a secret and add it to your authenticator app.</p>
                <button type="button" class="text-sm text-indigo-600 underline" wire:click="generateSecret">Generate secret</button>

                @if ($secret)
                    <div>
                        <p class="text-xs text-gray-500">Secret key</p>
                        <p class="font-mono text-gray-800">{{ $secret }}</p>
                        @if ($otpUrl)
                            <p class="text-xs text-gray-500">otpauth URL</p>
                            <p class="font-mono text-xs break-all">{{ $otpUrl }}</p>
                        @endif
                        <x-input-error class="mt-2" :messages="$errors->get('secret')" />
                    </div>
                    <div>
                        <x-input-label for="authCode" :value="__('Authenticator code')" />
                        <x-text-input wire:model="authCode" id="authCode" name="authCode" type="text" class="mt-1 block w-full" autocomplete="one-time-code" />
                        <x-input-error class="mt-2" :messages="$errors->get('authCode')" />
                    </div>
                @elseif ($configured)
                    <p class="text-xs text-gray-500">Authenticator is already enabled.</p>
                @elseif ($enabled)
                    <p class="text-xs text-gray-500">Generate a secret to finish setup.</p>
                @endif
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            <x-action-message class="me-3" on="mfa-updated">{{ __('Saved.') }}</x-action-message>
        </div>
    </form>
</section>
