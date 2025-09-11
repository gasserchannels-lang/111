<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && ! auth()->user()->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Email verification required'], 403);
            }

            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
