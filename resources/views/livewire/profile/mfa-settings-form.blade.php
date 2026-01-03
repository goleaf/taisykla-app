<?php

use App\Services\MfaService;
use Livewire\Attributes\On;
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
    public $securityKeys;

    public function mount(): void
    {
        $user = Auth::user();
        $this->enabled = (bool) $user->mfa_enabled;
        $this->method = in_array($user->mfa_method, ['email', 'auth_app', 'sms', 'security_key'], true)
            ? $user->mfa_method
            : 'email';
        $this->email = $user->mfa_email ?: $user->email;
        $this->phone = $user->mfa_phone ?: ($user->phone ?? '');
        $this->securityKeys = $user->securityKeys()->latest()->get();
        $this->configured = $user->mfa_enabled
            && $user->mfa_method === 'auth_app'
            && (bool) $user->mfa_secret;

        if ($user->mfa_method === 'security_key') {
            $this->configured = $this->securityKeys->isNotEmpty();
        }
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

    #[On('security-key-added')]
    public function refreshSecurityKeys(): void
    {
        $this->securityKeys = Auth::user()->securityKeys()->latest()->get();
        if ($this->method === 'security_key') {
            $this->configured = $this->securityKeys->isNotEmpty();
        }
    }

    public function removeSecurityKey(int $keyId): void
    {
        $user = Auth::user();
        $key = $user->securityKeys()->whereKey($keyId)->firstOrFail();
        $key->delete();

        $this->refreshSecurityKeys();

        if ($user->mfa_method === 'security_key' && $this->securityKeys->isEmpty()) {
            $user->update([
                'mfa_enabled' => false,
                'mfa_method' => null,
                'mfa_confirmed_at' => null,
            ]);
            $this->enabled = false;
            $this->method = 'email';
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
            'method' => ['required', Rule::in(['email', 'auth_app', 'sms', 'security_key'])],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ];

        if ($this->enabled && $this->method === 'email') {
            $rules['email'][] = 'required';
        }

        if ($this->enabled && $this->method === 'sms') {
            $rules['phone'][] = 'required';
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

        if ($this->method === 'sms') {
            $user->update([
                'mfa_enabled' => true,
                'mfa_method' => 'sms',
                'mfa_email' => null,
                'mfa_phone' => $this->phone,
                'mfa_secret' => null,
                'mfa_confirmed_at' => now(),
            ]);

            $this->configured = true;
            $this->dispatch('mfa-updated');
            return;
        }

        if ($this->method === 'security_key') {
            if ($this->securityKeys->isEmpty()) {
                $this->addError('method', 'Register at least one security key.');
                return;
            }

            $user->update([
                'mfa_enabled' => true,
                'mfa_method' => 'security_key',
                'mfa_email' => null,
                'mfa_phone' => null,
                'mfa_secret' => null,
                'mfa_confirmed_at' => now(),
            ]);

            $this->configured = true;
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
                <option value="sms">SMS</option>
                <option value="security_key">Security key</option>
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

        @if ($method === 'sms')
            <div>
                <x-input-label for="mfa_phone" :value="__('Phone number for verification codes')" />
                <x-text-input wire:model="phone" id="mfa_phone" name="mfa_phone" type="text" class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                <p class="mt-2 text-xs text-gray-500">SMS delivery uses the configured SMS gateway.</p>
            </div>
        @endif

        @if ($method === 'security_key')
            <div
                x-data="securityKeyRegistration()"
                class="rounded-md border border-gray-200 p-4 text-sm text-gray-700 space-y-3"
            >
                <p class="text-sm text-gray-600">Register a security key (FIDO2/WebAuthn) for passwordless verification.</p>
                <p class="text-xs text-gray-500">Security keys require HTTPS (or localhost) to register.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-2">
                        <x-input-label for="security_key_name" :value="__('Security key name')" />
                        <x-text-input x-model="name" id="security_key_name" type="text" class="mt-1 block w-full" placeholder="Office key" />
                    </div>
                    <div class="flex items-end">
                        <button type="button" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md" @click="register" :disabled="loading">
                            <span x-text="loading ? 'Registering...' : 'Register key'"></span>
                        </button>
                    </div>
                </div>

                <p class="text-xs text-red-600" x-text="error" x-show="error"></p>

                <div class="pt-2 border-t border-gray-100 space-y-2">
                    @if ($securityKeys && $securityKeys->count())
                        @foreach ($securityKeys as $key)
                            <div class="flex items-center justify-between text-sm">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $key->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        Added {{ $key->created_at?->diffForHumans() ?? 'recently' }}
                                        @if ($key->last_used_at)
                                            • Used {{ $key->last_used_at->diffForHumans() }}
                                        @endif
                                    </p>
                                </div>
                                <button type="button" class="text-xs text-red-600 underline" wire:click="removeSecurityKey({{ $key->id }})">
                                    Remove
                                </button>
                            </div>
                        @endforeach
                    @else
                        <p class="text-xs text-gray-500">No security keys registered yet.</p>
                    @endif
                </div>
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            <x-action-message class="me-3" on="mfa-updated">{{ __('Saved.') }}</x-action-message>
        </div>
    </form>
</section>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('securityKeyRegistration', () => ({
            name: '',
            loading: false,
            error: null,
            async register() {
                this.error = null;
                if (!window.PublicKeyCredential) {
                    this.error = 'Security keys are not supported in this browser.';
                    return;
                }

                this.loading = true;
                try {
                    const optionsResponse = await fetch(@json(route('security-keys.options')), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token()),
                        },
                    });
                    const optionsPayload = await optionsResponse.json();
                    if (!optionsResponse.ok) {
                        throw new Error(optionsPayload.message || 'Unable to start security key registration.');
                    }

                    const publicKey = optionsPayload.publicKey;
                    publicKey.challenge = this.base64urlToBuffer(publicKey.challenge);
                    publicKey.user.id = this.base64urlToBuffer(publicKey.user.id);
                    publicKey.excludeCredentials = (publicKey.excludeCredentials || []).map((credential) => ({
                        ...credential,
                        id: this.base64urlToBuffer(credential.id),
                    }));

                    const credential = await navigator.credentials.create({ publicKey });

                    const body = {
                        name: this.name || 'Security key',
                        id: credential.id,
                        rawId: this.bufferToBase64url(credential.rawId),
                        type: credential.type,
                        response: {
                            clientDataJSON: this.bufferToBase64url(credential.response.clientDataJSON),
                            attestationObject: this.bufferToBase64url(credential.response.attestationObject),
                        },
                        transports: credential.response.getTransports ? credential.response.getTransports() : [],
                    };

                    const registerResponse = await fetch(@json(route('security-keys.register')), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token()),
                        },
                        body: JSON.stringify(body),
                    });
                    const registerPayload = await registerResponse.json();
                    if (!registerResponse.ok) {
                        throw new Error(registerPayload.message || 'Security key registration failed.');
                    }

                    this.name = '';
                    if (window.Livewire) {
                        Livewire.dispatch('security-key-added');
                    }
                } catch (error) {
                    this.error = error?.message || 'Security key registration failed.';
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
