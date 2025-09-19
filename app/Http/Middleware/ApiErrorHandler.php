<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class ApiErrorHandler
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (Throwable $e) {
            return $this->handleException($request, $e);
        }
    }

    /**
     * Handle the exception and return appropriate JSON response.
     */
    private function handleException(Request $request, Throwable $e): JsonResponse
    {
        // Log the exception for debugging
        \Log::error('API Error: '.$e->getMessage(), [
            'exception' => $e,
            'request' => $request->all(),
            'url' => $request->url(),
            'method' => $request->method(),
        ]);

        // Handle specific exception types
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'error_code' => 'VALIDATION_ERROR',
            ], 422);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'error_code' => 'UNAUTHENTICATED',
            ], 401);
        }

        if ($e instanceof AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
                'error_code' => 'UNAUTHORIZED',
            ], 403);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'error_code' => 'NOT_FOUND',
            ], 404);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint not found',
                'error_code' => 'ENDPOINT_NOT_FOUND',
            ], 404);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed',
                'error_code' => 'METHOD_NOT_ALLOWED',
            ], 405);
        }

        if ($e instanceof TooManyRequestsHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
            ], 429);
        }

        // Handle database connection errors
        if ($e instanceof \PDOException) {
            return response()->json([
                'success' => false,
                'message' => 'Database connection error',
                'error_code' => 'DATABASE_ERROR',
            ], 503);
        }

        // Handle general exceptions
        return response()->json([
            'success' => false,
            'message' => app()->environment('production') ? 'Internal server error' : $e->getMessage(),
            'error_code' => 'INTERNAL_ERROR',
        ], 500);
    }
}
