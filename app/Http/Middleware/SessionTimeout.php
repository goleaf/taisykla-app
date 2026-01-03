<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $timeout = (int) config('session.idle_timeout', 0);
            $lastActivity = session('last_activity_at');

            if ($timeout > 0 && $lastActivity) {
                $last = Carbon::parse($lastActivity);
                if ($last->diffInMinutes(now()) >= $timeout) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')->with('status', 'Your session timed out due to inactivity.');
                }
            }

            session(['last_activity_at' => now()->toISOString()]);
        }

        return $next($request);
    }
}
