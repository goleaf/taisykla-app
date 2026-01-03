<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public bool $mustChangePassword = false;
    public bool $mfaEnabled = false;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user) {
            $this->redirectRoute('login', navigate: true);
            return;
        }

        $this->syncStatus($user);

        if (! $this->mustChangePassword && $user->onboarded_at) {
            $this->redirectRoute('dashboard', navigate: true);
        }
    }

    #[On('password-updated')]
    public function refreshPasswordStatus(): void
    {
        $this->refreshUserStatus();
    }

    #[On('mfa-updated')]
    public function refreshMfaStatus(): void
    {
        $this->refreshUserStatus();
    }

    public function finish(): void
    {
        $user = Auth::user();
        if (! $user) {
            $this->redirectRoute('login', navigate: true);
            return;
        }

        $user->refresh();
        if ($user->must_change_password) {
            $this->addError('finish', 'Please update your password before continuing.');
            return;
        }

        if (! $user->onboarded_at) {
            $user->forceFill(['onboarded_at' => now()])->save();
        }

        $this->redirectRoute('dashboard', navigate: true);
    }

    private function refreshUserStatus(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $user->refresh();
        $this->syncStatus($user);
    }

    private function syncStatus($user): void
    {
        $this->mustChangePassword = (bool) $user->must_change_password;
        $this->mfaEnabled = (bool) $user->mfa_enabled;
    }
}; ?>

<div class="py-10">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <h1 class="text-2xl font-semibold text-gray-900">Welcome to your account</h1>
            <p class="mt-1 text-sm text-gray-600">
                Set a new password and optionally enable multi-factor authentication.
            </p>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 text-sm">
                <div class="rounded-md border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Password</p>
                    <p class="mt-1 font-medium text-gray-900">
                        {{ $mustChangePassword ? 'Required' : 'Complete' }}
                    </p>
                    @if ($mustChangePassword)
                        <p class="mt-1 text-xs text-gray-500">Update your password to continue.</p>
                    @endif
                </div>
                <div class="rounded-md border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Multi-factor</p>
                    <p class="mt-1 font-medium text-gray-900">
                        {{ $mfaEnabled ? 'Enabled' : 'Optional' }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">You can enable MFA now or later.</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow sm:rounded-lg p-6">
            <livewire:profile.update-password-form />
        </div>

        <div class="bg-white shadow sm:rounded-lg p-6">
            <livewire:profile.mfa-settings-form />
        </div>

        <div class="flex items-center justify-between">
            <x-input-error class="mt-2" :messages="$errors->get('finish')" />
            <x-primary-button type="button" wire:click="finish" @disabled($mustChangePassword)>
                Finish setup
            </x-primary-button>
        </div>
    </div>
</div>
