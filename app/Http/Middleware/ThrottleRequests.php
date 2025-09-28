<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequests
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->ip();
        $maxAttempts = 60;
        $decayMinutes = 1; // 1 minute

        if (app('cache')->has("throttle:{$key}")) {
            $attempts = app('cache')->get("throttle:{$key}");
            if (is_numeric($attempts) && (int) $attempts >= $maxAttempts) {
                $retryAfter = $decayMinutes * 60; // Convert to seconds

                return response()->json(['message' => 'Too Many Requests'], 429)
                    ->header('Retry-After', $retryAfter);
            }
            app('cache')->put("throttle:{$key}", (is_numeric($attempts) ? (int) $attempts : 0) + 1, $decayMinutes);
        } else {
            app('cache')->put("throttle:{$key}", 1, $decayMinutes);
        }

        return $next($request);
    }
}
