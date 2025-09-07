<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $sessionId = $request->session()->getId();
            
            if ($user->session_id && $user->session_id !== $sessionId) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Session expired'], 401);
                }
                
                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}
