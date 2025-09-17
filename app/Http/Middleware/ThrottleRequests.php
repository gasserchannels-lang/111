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

        if (app('cache')->has("throttle:{$key}")) {
            $attempts = app('cache')->get("throttle:{$key}");
            if (is_numeric($attempts) && (int) $attempts >= $maxAttempts) {
                return response()->json(['message' => 'Too Many Requests'], 429);
            }
            app('cache')->put("throttle:{$key}", (is_numeric($attempts) ? (int) $attempts : 0) + 1, 60);
        } else {
            app('cache')->put("throttle:{$key}", 1, 60);
        }

        return $next($request);
    }
}
