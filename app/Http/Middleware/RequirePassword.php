<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePassword
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user && isset($user->password_confirmed_at) && $user->password_confirmed_at) {
                $lastConfirmation = $user->password_confirmed_at;
                $timeout = config('auth.password_timeout', 10800); // 3 hours default

                if (time() - $lastConfirmation->timestamp <= $timeout) {
                    return $next($request);
                }
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Password confirmation required'], 423);
        }

        return redirect()->route('password.confirm');
    }
}
