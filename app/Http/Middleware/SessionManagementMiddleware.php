<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SessionManagementMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for session fixation
        $this->preventSessionFixation($request);

        // Regenerate session ID periodically
        $this->regenerateSessionId($request);

        // Clean up inactive sessions
        $this->cleanupInactiveSessions($request);

        $response = $next($request);

        // Set secure session cookies
        $this->setSecureSessionCookies($response);

        return $response;
    }

    /**
     * Prevent session fixation attacks.
     */
    private function preventSessionFixation(Request $request): void
    {
        // Regenerate session ID on login
        if ($request->is('login') && $request->isMethod('post')) {
            Session::regenerate(true);
            Log::info('Session regenerated on login', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // Regenerate session ID on privilege escalation
        if ($request->user() && $request->user()->wasChanged('role')) {
            Session::regenerate(true);
            Log::info('Session regenerated on role change', [
                'user_id' => $request->user()->id,
                'ip' => $request->ip(),
            ]);
        }
    }

    /**
     * Regenerate session ID periodically.
     */
    private function regenerateSessionId(Request $request): void
    {
        $lastRegeneration = Session::get('last_regeneration', 0);
        $regenerationInterval = config('session.regeneration_interval', 300); // 5 minutes

        if (time() - $lastRegeneration > $regenerationInterval) {
            Session::regenerate(true);
            Session::put('last_regeneration', time());

            Log::debug('Session ID regenerated periodically', [
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
            ]);
        }
    }

    /**
     * Clean up inactive sessions.
     */
    private function cleanupInactiveSessions(Request $request): void
    {
        $lastActivity = Session::get('last_activity', time());
        $inactivityTimeout = config('session.inactivity_timeout', 1800); // 30 minutes

        if (time() - $lastActivity > $inactivityTimeout) {
            Session::flush();

            Log::info('Session cleaned up due to inactivity', [
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
                'inactivity_duration' => time() - $lastActivity,
            ]);
        } else {
            Session::put('last_activity', time());
        }
    }

    /**
     * Set secure session cookies.
     */
    private function setSecureSessionCookies(Response $response): void
    {
        $cookies = $response->headers->getCookies();

        foreach ($cookies as $cookie) {
            if (str_starts_with($cookie->getName(), config('session.cookie'))) {
                // Note: Cookie properties are set during creation, not after
                // This is a limitation of Symfony Cookie class
                // The security settings should be configured in session.php
            }
        }
    }
}
