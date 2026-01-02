<?php

use App\Livewire\Forms\LoginForm;
use App\Models\User;
use App\Services\MfaService;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;
    public array $demoUsers = [];

    /**
     * Load demo users for each role.
     */
    public function mount(): void
    {
        $roles = ['admin', 'dispatch', 'technician', 'support', 'client', 'guest'];
        $demoUsers = [];

        foreach ($roles as $role) {
            $user = User::role($role)->orderBy('name')->first();

            $demoUsers[] = [
                'role' => $role,
                'name' => $user?->name ?? 'Not seeded',
                'email' => $user?->email ?? 'Seed to create',
                'password' => 'password',
                'missing' => $user === null,
            ];
        }

        $this->demoUsers = $demoUsers;
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $user = $this->form->authenticate();
        $mfa = app(MfaService::class);
        if ($mfa->requiresMfa($user)) {
            $mfa->initiate($user);
            session([
                'mfa_user_id' => $user->id,
                'mfa_remember' => $this->form->remember,
            ]);
            $this->redirectRoute('mfa.challenge', navigate: true);
            return;
        }

        $this->form->login($user);

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6 border-t border-gray-200 pt-4">
        <h2 class="text-sm font-semibold text-gray-700">Demo users</h2>
        <p class="mt-1 text-xs text-gray-500">
            All demo accounts use the password <span class="font-mono text-gray-700">password</span>.
        </p>

        <div class="mt-3 space-y-2">
            @foreach ($demoUsers as $demoUser)
                @continue($demoUser['missing'])
                <div class="rounded-md border border-gray-200 px-3 py-2 text-xs text-gray-700">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold">{{ ucfirst($demoUser['role']) }}</span>
                    </div>
                    <div class="mt-1 text-gray-600">
                        Name: <span class="font-medium text-gray-700">{{ $demoUser['name'] }}</span>
                    </div>
                    <div class="text-gray-600">
                        Email: <span class="font-mono text-gray-700">{{ $demoUser['email'] }}</span>
                    </div>
                    <div class="text-gray-600">
                        Password: <span class="font-mono text-gray-700">{{ $demoUser['password'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
