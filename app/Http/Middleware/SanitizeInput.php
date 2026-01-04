<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * The names of the attributes that should not be sanitized.
     *
     * @var array<int, string>
     */
    protected array $except = [
        'current_password',
        'password',
        'password_confirmation',
        '_token',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        $this->clean($input);

        $request->merge($input);

        return $next($request);
    }

    /**
     * Sanitize the input array recursively.
     */
    protected function clean(array &$input): void
    {
        foreach ($input as $key => &$value) {
            if (in_array($key, $this->except, true)) {
                continue;
            }

            if (is_array($value)) {
                $this->clean($value);
            } elseif (is_string($value)) {
                $value = strip_tags($value);
            }
        }
    }
}
