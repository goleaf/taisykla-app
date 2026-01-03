<?php

namespace App\Services;

use App\Models\SecurityKey;
use App\Models\User;
use App\Support\CborDecoder;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SecurityKeyService
{
    public function beginRegistration(User $user): array
    {
        $challenge = $this->generateChallenge();

        session()->put('webauthn.register', [
            'challenge' => $challenge,
            'user_id' => $user->id,
        ]);

        $exclude = $user->securityKeys()
            ->get()
            ->map(fn (SecurityKey $key) => [
                'type' => 'public-key',
                'id' => $key->credential_id,
                'transports' => $key->transports ?? [],
            ])
            ->values()
            ->all();

        return [
            'publicKey' => [
                'challenge' => $challenge,
                'rp' => [
                    'name' => config('app.name', 'Maintenance System'),
                    'id' => $this->rpId(),
                ],
                'user' => [
                    'id' => $this->base64urlEncode((string) $user->id),
                    'name' => $user->email,
                    'displayName' => $user->name,
                ],
                'pubKeyCredParams' => [
                    ['type' => 'public-key', 'alg' => -7],
                ],
                'timeout' => 60000,
                'attestation' => 'none',
                'excludeCredentials' => $exclude,
            ],
        ];
    }

    public function finishRegistration(User $user, array $payload): SecurityKey
    {
        $session = session('webauthn.register', []);
        $expectedChallenge = $session['challenge'] ?? null;
        $sessionUserId = $session['user_id'] ?? null;

        if (! $expectedChallenge || (int) $sessionUserId !== $user->id) {
            throw new RuntimeException('Registration challenge expired.');
        }

        $clientDataJson = $this->base64urlDecode($payload['response']['clientDataJSON'] ?? '');
        $clientData = json_decode($clientDataJson, true);
        if (! is_array($clientData) || ($clientData['type'] ?? '') !== 'webauthn.create') {
            throw new RuntimeException('Invalid security key response.');
        }

        if (! hash_equals($expectedChallenge, (string) ($clientData['challenge'] ?? ''))) {
            throw new RuntimeException('Security key challenge mismatch.');
        }

        if (! hash_equals($this->origin(), (string) ($clientData['origin'] ?? ''))) {
            throw new RuntimeException('Security key origin mismatch.');
        }

        $attestationObject = $this->base64urlDecode($payload['response']['attestationObject'] ?? '');
        $decoded = CborDecoder::decode($attestationObject);
        $authData = $decoded['authData'] ?? null;
        if (! is_string($authData)) {
            throw new RuntimeException('Security key attestation data missing.');
        }

        $parsed = $this->parseAuthData($authData);
        $this->assertRpIdHash($parsed['rpIdHash']);

        if (! $parsed['credentialId'] || ! $parsed['credentialPublicKey']) {
            throw new RuntimeException('Security key credential data missing.');
        }

        $publicKey = $this->coseToPem($parsed['credentialPublicKey']);
        $credentialId = $this->base64urlEncode($parsed['credentialId']);

        session()->forget('webauthn.register');

        return SecurityKey::create([
            'user_id' => $user->id,
            'name' => $payload['name'] ?? 'Security key',
            'credential_id' => $credentialId,
            'public_key' => $publicKey,
            'sign_count' => $parsed['signCount'] ?? 0,
            'transports' => $payload['transports'] ?? [],
        ]);
    }

    public function beginAuthentication(User $user): array
    {
        $challenge = $this->generateChallenge();

        session()->put('webauthn.auth', [
            'challenge' => $challenge,
            'user_id' => $user->id,
        ]);

        $allowCredentials = $user->securityKeys()
            ->get()
            ->map(fn (SecurityKey $key) => [
                'type' => 'public-key',
                'id' => $key->credential_id,
                'transports' => $key->transports ?? [],
            ])
            ->values()
            ->all();

        return [
            'publicKey' => [
                'challenge' => $challenge,
                'rpId' => $this->rpId(),
                'timeout' => 60000,
                'allowCredentials' => $allowCredentials,
                'userVerification' => 'preferred',
            ],
        ];
    }

    public function finishAuthentication(User $user, array $payload): SecurityKey
    {
        $session = session('webauthn.auth', []);
        $expectedChallenge = $session['challenge'] ?? null;
        $sessionUserId = $session['user_id'] ?? null;

        if (! $expectedChallenge || (int) $sessionUserId !== $user->id) {
            throw new RuntimeException('Authentication challenge expired.');
        }

        $clientDataJson = $this->base64urlDecode($payload['response']['clientDataJSON'] ?? '');
        $clientData = json_decode($clientDataJson, true);
        if (! is_array($clientData) || ($clientData['type'] ?? '') !== 'webauthn.get') {
            throw new RuntimeException('Invalid security key response.');
        }

        if (! hash_equals($expectedChallenge, (string) ($clientData['challenge'] ?? ''))) {
            throw new RuntimeException('Security key challenge mismatch.');
        }

        if (! hash_equals($this->origin(), (string) ($clientData['origin'] ?? ''))) {
            throw new RuntimeException('Security key origin mismatch.');
        }

        $credentialId = (string) ($payload['id'] ?? '');
        $securityKey = $user->securityKeys()->where('credential_id', $credentialId)->first();
        if (! $securityKey) {
            throw new RuntimeException('Security key not recognized.');
        }

        $authenticatorData = $this->base64urlDecode($payload['response']['authenticatorData'] ?? '');
        $parsed = $this->parseAuthData($authenticatorData);
        $this->assertRpIdHash($parsed['rpIdHash']);

        $flags = $parsed['flags'];
        if (($flags & 0x01) === 0) {
            throw new RuntimeException('Security key user presence required.');
        }

        $signature = $this->base64urlDecode($payload['response']['signature'] ?? '');
        $clientDataHash = hash('sha256', $clientDataJson, true);
        $signedData = $authenticatorData . $clientDataHash;

        $verified = openssl_verify($signedData, $signature, $securityKey->public_key, OPENSSL_ALGO_SHA256);
        if ($verified !== 1) {
            throw new RuntimeException('Security key verification failed.');
        }

        if ($parsed['signCount'] > 0 && $securityKey->sign_count > 0 && $parsed['signCount'] <= $securityKey->sign_count) {
            Log::warning('Security key counter did not increase.', ['user_id' => $user->id, 'key_id' => $securityKey->id]);
            throw new RuntimeException('Security key counter check failed.');
        }

        $securityKey->update([
            'sign_count' => max($securityKey->sign_count, $parsed['signCount']),
            'last_used_at' => now(),
        ]);

        session()->forget('webauthn.auth');

        return $securityKey;
    }

    private function parseAuthData(string $authData): array
    {
        if (strlen($authData) < 37) {
            throw new RuntimeException('Invalid authenticator data.');
        }

        $rpIdHash = substr($authData, 0, 32);
        $flags = ord($authData[32]);
        $signCount = unpack('N', substr($authData, 33, 4))[1];
        $offset = 37;

        $credentialId = null;
        $credentialPublicKey = null;

        if (($flags & 0x40) !== 0) {
            if (strlen($authData) < $offset + 18) {
                throw new RuntimeException('Invalid credential data.');
            }

            $offset += 16;
            $credLen = unpack('n', substr($authData, $offset, 2))[1];
            $offset += 2;
            $credentialId = substr($authData, $offset, $credLen);
            $offset += $credLen;

            $credentialPublicKey = CborDecoder::decode(substr($authData, $offset));
        }

        return [
            'rpIdHash' => $rpIdHash,
            'flags' => $flags,
            'signCount' => $signCount,
            'credentialId' => $credentialId,
            'credentialPublicKey' => $credentialPublicKey,
        ];
    }

    private function coseToPem(array $cose): string
    {
        $kty = $cose[1] ?? null;
        $alg = $cose[3] ?? null;
        $crv = $cose[-1] ?? null;
        $x = $cose[-2] ?? null;
        $y = $cose[-3] ?? null;

        if ($kty !== 2 || $alg !== -7 || $crv !== 1 || ! is_string($x) || ! is_string($y)) {
            throw new RuntimeException('Unsupported security key type.');
        }

        $x = str_pad($x, 32, "\0", STR_PAD_LEFT);
        $y = str_pad($y, 32, "\0", STR_PAD_LEFT);
        $publicKey = "\x04" . $x . $y;

        $der = "\x30\x59\x30\x13\x06\x07\x2A\x86\x48\xCE\x3D\x02\x01\x06\x08\x2A\x86\x48\xCE\x3D\x03\x01\x07\x03\x42\x00" . $publicKey;

        return "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(base64_encode($der), 64, "\n")
            . "-----END PUBLIC KEY-----\n";
    }

    private function assertRpIdHash(string $rpIdHash): void
    {
        $expected = hash('sha256', $this->rpId(), true);
        if (! hash_equals($expected, $rpIdHash)) {
            throw new RuntimeException('Security key rpId mismatch.');
        }
    }

    private function generateChallenge(): string
    {
        return $this->base64urlEncode(random_bytes(32));
    }

    private function origin(): string
    {
        return rtrim((string) config('app.url'), '/');
    }

    private function rpId(): string
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);
        return $host ?: 'localhost';
    }

    private function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64urlDecode(string $data): string
    {
        $data = strtr($data, '-_', '+/');
        $padding = strlen($data) % 4;
        if ($padding !== 0) {
            $data .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            throw new RuntimeException('Invalid base64 data.');
        }

        return $decoded;
    }
}
