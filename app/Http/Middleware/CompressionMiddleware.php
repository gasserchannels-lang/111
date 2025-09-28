<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CompressionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! $this->shouldCompress($request, $response)) {
            return $response;
        }

        $content = $response->getContent();
        $compressed = $this->compressContent($content, $request);

        if ($compressed !== false) {
            $response->setContent($compressed);
            $response->headers->set('Content-Encoding', 'gzip');
            $response->headers->set('Content-Length', strlen($compressed));
            $response->headers->set('Vary', 'Accept-Encoding');
        }

        return $response;
    }

    private function shouldCompress(Request $request, Response $response): bool
    {
        // Check if client accepts gzip
        if (! str_contains($request->header('Accept-Encoding', ''), 'gzip')) {
            return false;
        }

        // Check content type
        $contentType = $response->headers->get('Content-Type', '');
        $compressibleTypes = [
            'text/html',
            'text/css',
            'text/javascript',
            'application/javascript',
            'application/json',
            'application/xml',
            'text/xml',
        ];

        $shouldCompress = false;
        foreach ($compressibleTypes as $type) {
            if (str_contains($contentType, $type)) {
                $shouldCompress = true;
                break;
            }
        }

        if (! $shouldCompress) {
            return false;
        }

        // Check content length (only compress if > 1KB)
        return strlen($response->getContent()) > 1024;
    }

    private function compressContent(string $content, Request $request): string|false
    {
        // Try Brotli first if supported
        if (function_exists('brotli_compress') && str_contains($request->header('Accept-Encoding', ''), 'br')) {
            $compressed = brotli_compress($content, 6);
            if ($compressed !== false) {
                return $compressed;
            }
        }

        // Fallback to Gzip
        if (function_exists('gzencode')) {
            return gzencode($content, 6);
        }

        return false;
    }
}
