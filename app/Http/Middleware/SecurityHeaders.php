<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Security headers that should be applied.
     *
     * @var array<string, string>
     */
    private array $securityHeaders = [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';",
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        'X-Permitted-Cross-Domain-Policies' => 'none',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add security headers
        foreach ($this->securityHeaders as $key => $value) {
            $response->headers->set($key, $value);
        }

        // Force HTTPS in production
        if (app()->environment('production') && ! $request->secure()) {
            return redirect()->secure($request->getRequestUri());
        }

        // Prevent clickjacking for sensitive routes
        if ($this->isSensitiveRoute($request)) {
            $response->headers->set('X-Frame-Options', 'DENY');
        }

        // Log suspicious activities
        if ($this->isSuspiciousRequest($request)) {
            Log::warning('Suspicious request detected', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'uri' => $request->getRequestUri(),
                'payload' => $request->except(['password', 'password_confirmation']),
            ]);
        }

        return $response;
    }

    /**
     * Check if the current route is sensitive.
     */
    private function isSensitiveRoute(Request $request): bool
    {
        $sensitivePaths = [
            'admin/*',
            'settings/*',
            'profile/*',
            'billing/*',
            'api/v1/admin/*',
        ];

        foreach ($sensitivePaths as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the request looks suspicious.
     */
    private function isSuspiciousRequest(Request $request): bool
    {
        // Check for common SQL injection patterns
        $sqlPatterns = [
            'union select',
            'union all select',
            'from information_schema',
            'exec(',
            'eval(',
            ';',
            '-- ',
            '/*',
            '*/',
            'xp_cmdshell',
            'drop table',
            'drop database',
        ];

        $input = strtolower(json_encode($request->all()) ?: '');
        foreach ($sqlPatterns as $pattern) {
            if (str_contains($input, $pattern)) {
                return true;
            }
        }

        // Check for XSS patterns
        $xssPatterns = [
            '<script',
            'javascript:',
            'onerror=',
            'onload=',
            'onclick=',
            'onmouseover=',
        ];

        foreach ($xssPatterns as $pattern) {
            if (str_contains($input, $pattern)) {
                return true;
            }
        }

        // Check for suspicious file uploads
        if ($request->hasFile('*')) {
            $suspiciousExtensions = ['.php', '.phtml', '.phar', '.htaccess', '.env'];
            foreach ($request->allFiles() as $file) {
                if (is_array($file)) {
                    foreach ($file as $singleFile) {
                        $fileName = strtolower($singleFile->getClientOriginalName());
                        foreach ($suspiciousExtensions as $ext) {
                            if (str_contains($fileName, $ext)) {
                                return true;
                            }
                        }
                    }
                } else {
                    $fileName = strtolower($file->getClientOriginalName());
                    foreach ($suspiciousExtensions as $ext) {
                        if (str_contains($fileName, $ext)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}
