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
        if (in_array($this->method, ['auth_app', 'email', 'sms'], true)) {
            $rules['code'][] = 'digits:6';
        }
        if (in_array($this->method, ['auth_app', 'email', 'sms'], true)) {
            $this->validate($rules);
        }

        $user = $this->challengeUser();
        if (! $user) {
            $this->redirectRoute('login', navigate: true);
            return;
        }

        $mfa = app(MfaService::class);
        $valid = match ($this->method) {
            'auth_app' => $mfa->verifyTotp($user, $this->code),
            'sms' => $mfa->verifySmsCode($user, $this->code),
            default => $mfa->verifyEmailCode($user, $this->code),
        };

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

        if ($this->method === 'sms') {
            app(MfaService::class)->sendSmsCode($user);
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

        if ($user->mfa_method === 'sms') {
            $phone = $user->mfa_phone ?: $user->phone;
            if (! $phone) {
                return 'your phone';
            }

            $digits = preg_replace('/\D+/', '', $phone);
            if (! $digits || strlen($digits) < 4) {
                return $phone;
            }

            return str_repeat('*', max(strlen($digits) - 4, 0)) . substr($digits, -4);
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
        @elseif ($method === 'security_key')
            Use your security key to complete sign-in.
        @else
            Enter the verification code sent to {{ $destination }}.
        @endif
    </div>

    @if ($method !== 'security_key')
        <form wire:submit="verify">
            <div>
                <x-input-label for="code" :value="__('Verification Code')" />
                <x-text-input wire:model="code" id="code" class="block mt-1 w-full" type="text" name="code" required autofocus autocomplete="one-time-code" />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mt-4">
                @if (in_array($method, ['email', 'sms'], true))
                    <button type="button" class="text-sm text-gray-600 underline" wire:click="resend">Resend code</button>
                @endif
                <x-primary-button>
                    {{ __('Verify') }}
                </x-primary-button>
            </div>
        </form>
    @else
        <div
            x-data="securityKeyChallenge()"
            class="rounded-md border border-gray-200 p-4"
        >
            <p class="text-sm text-gray-600">
                Insert or tap your security key, then press the button below.
            </p>
            <div class="mt-4 flex items-center gap-3">
                <button type="button" class="px-4 py-2 bg-indigo-600 text-white rounded-md" @click="start" :disabled="loading">
                    <span x-text="loading ? 'Waiting for security key...' : 'Use Security Key'"></span>
                </button>
                <span class="text-xs text-red-600" x-text="error" x-show="error"></span>
            </div>
        </div>
    @endif
</div>

@if ($method === 'security_key')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('securityKeyChallenge', () => ({
                loading: false,
                error: null,
                async start() {
                    this.error = null;
                    if (!window.PublicKeyCredential) {
                        this.error = 'Security keys are not supported in this browser.';
                        return;
                    }

                    this.loading = true;
                    try {
                        const optionsResponse = await fetch(@json(route('security-keys.authentication.options')), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': @json(csrf_token()),
                            },
                        });
                        const optionsPayload = await optionsResponse.json();
                        if (!optionsResponse.ok) {
                            throw new Error(optionsPayload.message || 'Unable to start security key verification.');
                        }

                        const publicKey = optionsPayload.publicKey;
                        publicKey.challenge = this.base64urlToBuffer(publicKey.challenge);
                        publicKey.allowCredentials = (publicKey.allowCredentials || []).map((credential) => ({
                            ...credential,
                            id: this.base64urlToBuffer(credential.id),
                        }));

                        const credential = await navigator.credentials.get({ publicKey });

                        const body = {
                            id: credential.id,
                            rawId: this.bufferToBase64url(credential.rawId),
                            type: credential.type,
                            response: {
                                authenticatorData: this.bufferToBase64url(credential.response.authenticatorData),
                                clientDataJSON: this.bufferToBase64url(credential.response.clientDataJSON),
                                signature: this.bufferToBase64url(credential.response.signature),
                                userHandle: credential.response.userHandle
                                    ? this.bufferToBase64url(credential.response.userHandle)
                                    : null,
                            },
                        };

                        const verifyResponse = await fetch(@json(route('security-keys.authentication.verify')), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': @json(csrf_token()),
                            },
                            body: JSON.stringify(body),
                        });
                        const verifyPayload = await verifyResponse.json();
                        if (!verifyResponse.ok) {
                            throw new Error(verifyPayload.message || 'Security key verification failed.');
                        }

                        if (verifyPayload.redirect) {
                            window.location.href = verifyPayload.redirect;
                        } else {
                            window.location.reload();
                        }
                    } catch (error) {
                        this.error = error?.message || 'Security key verification failed.';
                    } finally {
                        this.loading = false;
                    }
                },
                base64urlToBuffer(value) {
                    const padding = '='.repeat((4 - (value.length % 4)) % 4);
                    const base64 = (value + padding).replace(/-/g, '+').replace(/_/g, '/');
                    const raw = atob(base64);
                    const buffer = new Uint8Array(raw.length);
                    for (let i = 0; i < raw.length; i += 1) {
                        buffer[i] = raw.charCodeAt(i);
                    }
                    return buffer;
                },
                bufferToBase64url(buffer) {
                    const bytes = new Uint8Array(buffer);
                    let binary = '';
                    bytes.forEach((byte) => {
                        binary += String.fromCharCode(byte);
                    });
                    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
                },
            }));
        });
    </script>
@endif
