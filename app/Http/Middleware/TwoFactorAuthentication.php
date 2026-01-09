<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuthentication
{
    /**
     * Routes that should be accessible during 2FA verification.
     */
    protected array $excludedRoutes = [
        '2fa/verify',
        '2fa/verify/*',
        'logout',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Skip if not authenticated
        if (!$user) {
            return $next($request);
        }

        // Skip excluded routes
        foreach ($this->excludedRoutes as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        // Check if 2FA is pending verification
        if (session()->has('2fa:user:id')) {
            return redirect()->route('2fa.verify');
        }

        return $next($request);
    }
}
