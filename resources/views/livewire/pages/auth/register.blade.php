<?php

use App\Models\User;
use App\Support\RoleCatalog;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.guest')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));
        Role::firstOrCreate(['name' => RoleCatalog::CONSUMER]);
        $user->assignRole(RoleCatalog::CONSUMER);
        $user->passwordHistories()->create(['password_hash' => $user->password]);

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="auth-header">
        <a href="/" class="mobile-logo">
            <div class="mobile-logo-icon">T</div>
            <span>Taisykla</span>
        </a>
        <h2>Create your account</h2>
        <p>Start managing your equipment maintenance today</p>
    </div>

    <div class="auth-card">
        <form wire:submit="register">
            <!-- Name -->
            <div class="form-group">
                <label for="name" class="form-label">Full name</label>
                <input wire:model="name" id="name" type="text" name="name" class="form-input" placeholder="John Doe"
                    required autofocus autocomplete="name">
                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="form-group">
                <label for="email" class="form-label">Email address</label>
                <input wire:model="email" id="email" type="email" name="email" class="form-input"
                    placeholder="you@example.com" required autocomplete="username">
                @error('email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input wire:model="password" id="password" type="password" name="password" class="form-input"
                    placeholder="••••••••••••" required autocomplete="new-password">
                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="password-requirements">
                    Password must include:
                    <ul>
                        <li>At least 12 characters</li>
                        <li>Uppercase and lowercase letters</li>
                        <li>At least one number</li>
                        <li>At least one symbol</li>
                    </ul>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirm password</label>
                <input wire:model="password_confirmation" id="password_confirmation" type="password"
                    name="password_confirmation" class="form-input" placeholder="••••••••••••" required
                    autocomplete="new-password">
                @error('password_confirmation')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-primary" wire:loading.attr="disabled" style="margin-top: 24px;">
                <span wire:loading.remove>Create Account</span>
                <span wire:loading>Creating account...</span>
            </button>
        </form>

        <!-- Terms Notice -->
        <p style="margin-top: 16px; font-size: 12px; color: #64748b; text-align: center; line-height: 1.6;">
            By creating an account, you agree to our
            <a href="{{ route('terms') }}" class="link" style="font-size: 12px;">Terms of Service</a>
            and
            <a href="{{ route('privacy') }}" class="link" style="font-size: 12px;">Privacy Policy</a>.
        </p>
    </div>

    <div class="auth-footer">
        Already have an account? <a href="{{ route('login') }}" wire:navigate>Sign in</a>
    </div>
</div>