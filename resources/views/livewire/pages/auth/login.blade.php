<?php

use App\Livewire\Forms\LoginForm;
use App\Models\User;
use App\Services\MfaService;
use App\Support\RoleCatalog;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;
    public array $demoUsers = [];

    /**
     * Load demo users for each role.
     */
    public function mount(): void
    {
        $roles = RoleCatalog::all();
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
    <div class="auth-header">
        <a href="/" class="mobile-logo">
            <div class="mobile-logo-icon">T</div>
            <span>Taisykla</span>
        </a>
        <h2>Welcome back</h2>
        <p>Sign in to continue to your dashboard</p>
    </div>

    <div class="auth-card">
        <!-- Session Status -->
        @if (session('status'))
            <div class="status-message success">
                {{ session('status') }}
            </div>
        @endif

        <form wire:submit="login">
            <!-- Email Address -->
            <div class="form-group">
                <label for="email" class="form-label">Email address</label>
                <input wire:model="form.email" id="email" type="email" name="email" class="form-input"
                    placeholder="you@example.com" required autofocus autocomplete="username">
                @error('form.email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input wire:model="form.password" id="password" type="password" name="password" class="form-input"
                    placeholder="••••••••••••" required autocomplete="current-password">
                @error('form.password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="form-row">
                <label for="remember" class="checkbox-label">
                    <input wire:model="form.remember" id="remember" type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="link" href="{{ route('password.request') }}" wire:navigate>
                        Forgot password?
                    </a>
                @endif
            </div>

            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove>Sign In</span>
                <span wire:loading>Signing in...</span>
            </button>
        </form>

        <!-- Demo Users Section -->
        @if (count($demoUsers) > 0)
            <div class="demo-section">
                <div class="demo-header">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3>Demo Accounts</h3>
                </div>
                <p class="demo-note">
                    All demo accounts use password: <code>password</code>
                </p>
                <div class="demo-grid">
                    @foreach ($demoUsers as $demoUser)
                        @continue($demoUser['missing'])
                        <div class="demo-user">
                            <div class="demo-user-role">{{ RoleCatalog::label($demoUser['role']) }}</div>
                            <div class="demo-user-info">
                                <div><span>{{ $demoUser['name'] }}</span></div>
                                <div><code>{{ $demoUser['email'] }}</code></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="auth-footer">
        Don't have an account? <a href="{{ route('register') }}" wire:navigate>Create one</a>
    </div>
</div>