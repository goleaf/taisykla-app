<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAccountSetup
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->must_change_password || ! $user->onboarded_at) {
            if ($request->routeIs('onboarding')) {
                return $next($request);
            }

            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}
