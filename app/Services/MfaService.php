<?php

namespace App\Services;

use App\Models\MfaChallenge;
use App\Models\User;
use App\Notifications\MfaCodeNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class MfaService
{
    public function requiresMfa(User $user): bool
    {
        if (! $user->mfa_enabled || (string) $user->mfa_method === '') {
            return false;
        }

        if ($user->mfa_method === 'auth_app') {
            return (bool) $user->mfa_secret;
        }

        return true;
    }

    public function initiate(User $user): void
    {
        if (! $this->requiresMfa($user)) {
            return;
        }

        if ($user->mfa_method === 'email') {
            $this->sendEmailCode($user);
        }
    }

    public function sendEmailCode(User $user): void
    {
        $this->consumeOpenChallenges($user);

        $code = (string) random_int(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(10);

        MfaChallenge::create([
            'user_id' => $user->id,
            'method' => 'email',
            'code_hash' => Hash::make($code),
            'expires_at' => $expiresAt,
        ]);

        Notification::route('mail', $user->mfa_email ?: $user->email)
            ->notify(new MfaCodeNotification($code, $expiresAt));
    }

    public function verifyEmailCode(User $user, string $code): bool
    {
        $challenge = MfaChallenge::where('user_id', $user->id)
            ->where('method', 'email')
            ->whereNull('consumed_at')
            ->where('expires_at', '>=', now())
            ->latest('id')
            ->first();

        if (! $challenge || ! Hash::check($code, $challenge->code_hash)) {
            return false;
        }

        $challenge->update(['consumed_at' => now()]);
        return true;
    }

    public function generateSecret(): string
    {
        return $this->base32Encode(random_bytes(10));
    }

    public function verifyTotp(User $user, string $code): bool
    {
        if (! $user->mfa_secret) {
            return false;
        }

        return $this->verifyTotpSecret($user->mfa_secret, $code);
    }

    public function verifyTotpSecret(string $secret, string $code): bool
    {
        $code = trim($code);
        if (! ctype_digit($code) || strlen($code) !== 6) {
            return false;
        }

        $time = time();
        for ($offset = -1; $offset <= 1; $offset++) {
            $generated = $this->totp($secret, $time + ($offset * 30));
            if (hash_equals($generated, $code)) {
                return true;
            }
        }

        return false;
    }

    public function otpauthUrl(User $user, string $secret): string
    {
        $label = urlencode($user->email);
        $issuer = urlencode(config('app.name', 'Maintenance System'));

        return "otpauth://totp/{$issuer}:{$label}?secret={$secret}&issuer={$issuer}";
    }

    private function consumeOpenChallenges(User $user): void
    {
        MfaChallenge::where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);
    }

    private function totp(string $secret, int $timestamp): string
    {
        $counter = intdiv($timestamp, 30);
        $binaryCounter = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $binaryCounter, $this->base32Decode($secret), true);
        $offset = ord($hash[19]) & 0xf;
        $value = ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);
        $code = $value % 1000000;

        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';

        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $chunks = str_split($binary, 5);
        $encoded = '';
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $encoded .= $alphabet[bindec($chunk)];
        }

        return $encoded;
    }

    private function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper($secret);
        $secret = preg_replace('/[^A-Z2-7]/', '', $secret);

        $binary = '';
        foreach (str_split($secret) as $char) {
            $index = strpos($alphabet, $char);
            if ($index === false) {
                continue;
            }
            $binary .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        foreach (str_split($binary, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(bindec($chunk));
            }
        }

        return $bytes;
    }
}
