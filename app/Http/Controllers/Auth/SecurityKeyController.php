<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SecurityKey;
use App\Models\User;
use App\Services\SecurityKeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class SecurityKeyController extends Controller
{
    public function registrationOptions(Request $request, SecurityKeyService $service)
    {
        $user = $request->user();

        return response()->json($service->beginRegistration($user));
    }

    public function register(Request $request, SecurityKeyService $service)
    {
        $payload = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'id' => ['required', 'string'],
            'rawId' => ['required', 'string'],
            'type' => ['required', 'string'],
            'response' => ['required', 'array'],
            'response.clientDataJSON' => ['required', 'string'],
            'response.attestationObject' => ['required', 'string'],
            'transports' => ['nullable', 'array'],
        ]);

        try {
            $key = $service->finishRegistration($request->user(), $payload);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'status' => 'ok',
            'key' => [
                'id' => $key->id,
                'name' => $key->name,
            ],
        ]);
    }

    public function authenticationOptions(Request $request, SecurityKeyService $service)
    {
        $user = $this->mfaUser();
        if (! $user) {
            return response()->json(['message' => 'Authentication session expired.'], 403);
        }

        if (! $user->securityKeys()->exists()) {
            return response()->json(['message' => 'No security keys registered.'], 422);
        }

        return response()->json($service->beginAuthentication($user));
    }

    public function authenticate(Request $request, SecurityKeyService $service)
    {
        $payload = $request->validate([
            'id' => ['required', 'string'],
            'rawId' => ['required', 'string'],
            'type' => ['required', 'string'],
            'response' => ['required', 'array'],
            'response.clientDataJSON' => ['required', 'string'],
            'response.authenticatorData' => ['required', 'string'],
            'response.signature' => ['required', 'string'],
            'response.userHandle' => ['nullable', 'string'],
        ]);

        $user = $this->mfaUser();
        if (! $user) {
            return response()->json(['message' => 'Authentication session expired.'], 403);
        }

        try {
            $service->finishAuthentication($user, $payload);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        Auth::login($user, (bool) session('mfa_remember', false));
        session()->forget(['mfa_user_id', 'mfa_remember']);
        $request->session()->regenerate();

        return response()->json([
            'status' => 'ok',
            'redirect' => route('dashboard', absolute: false),
        ]);
    }

    public function destroy(Request $request, SecurityKey $securityKey)
    {
        if ($request->user()->id !== $securityKey->user_id) {
            abort(403);
        }

        $securityKey->delete();

        return response()->json(['status' => 'ok']);
    }

    private function mfaUser(): ?User
    {
        $userId = session('mfa_user_id');
        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }
}
