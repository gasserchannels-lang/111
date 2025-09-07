<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidatePostSize
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $maxSize = 8 * 1024 * 1024; // 8MB
            if ($request->header('Content-Length') > $maxSize) {
                return response()->json(['message' => 'Request entity too large'], 413);
            }
        }

        return $next($request);
    }
}
