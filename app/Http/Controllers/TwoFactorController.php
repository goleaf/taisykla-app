<?php

namespace App\Http\Controllers;

use App\Notifications\TwoFactorDisabledNotification;
use App\Notifications\TwoFactorEnabledNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Show the 2FA setup form with QR code.
     */
    public function showEnableForm()
    {
        $user = auth()->user();

        if ($user->google2fa_enabled) {
            return redirect()->route('profile')
                ->with('info', 'Two-factor authentication is already enabled.');
        }

        // Generate secret if not exists
        $secret = $user->google2fa_secret ?? $this->google2fa->generateSecretKey();

        // Temporarily store secret (not confirmed yet)
        if (!$user->google2fa_secret) {
            $user->update(['google2fa_secret' => $secret]);
        }

        // Generate QR code URL
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('auth.two-factor.enable', [
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $secret,
        ]);
    }

    /**
     * Enable 2FA after verifying the code.
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'password' => 'required|current_password',
        ]);

        $user = auth()->user();

        // Verify the code
        $valid = $this->google2fa->verifyKey($user->google2fa_secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid verification code. Please try again.']);
        }

        // Generate backup codes
        $backupCodes = $this->generateBackupCodes();
        $hashedCodes = array_map(fn($code) => Hash::make($code), $backupCodes);

        // Enable 2FA
        $user->update([
            'google2fa_enabled' => true,
            'backup_codes' => $hashedCodes,
            'two_factor_confirmed_at' => now(),
        ]);

        // Send notification
        $user->notify(new TwoFactorEnabledNotification());

        return view('auth.two-factor.backup-codes', [
            'backupCodes' => $backupCodes,
        ]);
    }

    /**
     * Show 2FA verification form during login.
     */
    public function showVerifyForm()
    {
        if (!session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor.verify');
    }

    /**
     * Verify 2FA code during login.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = session()->get('2fa:user:id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::findOrFail($userId);
        $code = $request->code;

        // Check if it's a backup code (8 chars) or TOTP code (6 chars)
        if (strlen($code) === 8) {
            // Validate backup code
            $backupCodes = $user->backup_codes ?? [];
            $validCode = false;
            $updatedCodes = [];

            foreach ($backupCodes as $index => $hashedCode) {
                if (Hash::check($code, $hashedCode)) {
                    $validCode = true;
                    // Remove used code
                    continue;
                }
                $updatedCodes[] = $hashedCode;
            }

            if (!$validCode) {
                return back()->withErrors(['code' => 'Invalid backup code.']);
            }

            // Update remaining backup codes
            $user->update(['backup_codes' => $updatedCodes]);
        } else {
            // Validate TOTP code
            $valid = $this->google2fa->verifyKey($user->google2fa_secret, $code);

            if (!$valid) {
                return back()->withErrors(['code' => 'Invalid verification code.']);
            }
        }

        // Clear 2FA session
        session()->forget('2fa:user:id');

        // Complete login
        auth()->login($user);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = auth()->user();

        $user->update([
            'google2fa_enabled' => false,
            'google2fa_secret' => null,
            'backup_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        // Send notification
        $user->notify(new TwoFactorDisabledNotification());

        return redirect()->route('profile')
            ->with('success', 'Two-factor authentication has been disabled.');
    }

    /**
     * Regenerate backup codes.
     */
    public function regenerateBackupCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = auth()->user();

        if (!$user->google2fa_enabled) {
            return back()->withErrors(['error' => '2FA is not enabled.']);
        }

        $backupCodes = $this->generateBackupCodes();
        $hashedCodes = array_map(fn($code) => Hash::make($code), $backupCodes);

        $user->update(['backup_codes' => $hashedCodes]);

        return view('auth.two-factor.backup-codes', [
            'backupCodes' => $backupCodes,
            'regenerated' => true,
        ]);
    }

    /**
     * Generate 8 backup codes.
     */
    protected function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(Str::random(8));
        }
        return $codes;
    }
}
