<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add security headers
        $this->addSecurityHeaders($response);

        return $response;
    }

    /**
     * Add security headers to response.
     */
    private function addSecurityHeaders(Response $response): void
    {
        // X-Frame-Options: Prevent clickjacking
        $xFrameOptions = config('security.headers.X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Frame-Options', is_string($xFrameOptions) ? $xFrameOptions : 'SAMEORIGIN');

        // X-Content-Type-Options: Prevent MIME type sniffing
        $xContentTypeOptions = config('security.headers.X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Content-Type-Options', is_string($xContentTypeOptions) ? $xContentTypeOptions : 'nosniff');

        // X-XSS-Protection: Enable XSS filtering
        $xXSSProtection = config('security.headers.X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-XSS-Protection', is_string($xXSSProtection) ? $xXSSProtection : '1; mode=block');

        // Referrer-Policy: Control referrer information
        $referrerPolicy = config('security.headers.Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Referrer-Policy', is_string($referrerPolicy) ? $referrerPolicy : 'strict-origin-when-cross-origin');

        // Content-Security-Policy: Prevent XSS attacks
        $this->addContentSecurityPolicy($response);

        // Strict-Transport-Security: Force HTTPS
        $this->addStrictTransportSecurity($response);

        // Permissions-Policy: Control browser features
        $this->addPermissionsPolicy($response);

        // X-Permitted-Cross-Domain-Policies: Control cross-domain policies
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // Cross-Origin-Embedder-Policy: Control embedding
        $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');

        // Cross-Origin-Opener-Policy: Control opener
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        // Cross-Origin-Resource-Policy: Control resource sharing
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
    }

    /**
     * Add Content Security Policy header.
     */
    private function addContentSecurityPolicy(Response $response): void
    {
        $csp = config('security.headers.Content-Security-Policy');
        if ($csp && is_string($csp)) {
            $response->headers->set('Content-Security-Policy', $csp);
        }
    }

    /**
     * Add Strict-Transport-Security header.
     */
    private function addStrictTransportSecurity(Response $response): void
    {
        if (request()->isSecure()) {
            $sts = config('security.headers.Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
            $response->headers->set(
                'Strict-Transport-Security',
                is_string($sts) ? $sts : 'max-age=31536000; includeSubDomains; preload'
            );
        }
    }

    /**
     * Add Permissions-Policy header.
     */
    private function addPermissionsPolicy(Response $response): void
    {
        $permissions = config('security.headers.Permissions-Policy');
        if ($permissions && is_string($permissions)) {
            $response->headers->set('Permissions-Policy', $permissions);
        }
    }
}
