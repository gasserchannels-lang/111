<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GlobalExceptionHandler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): JsonResponse|\Illuminate\Http\Response
    {
        // Handle API requests
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->handleApiException($request, $e);
        }

        // Handle web requests
        return $this->handleWebException($request, $e);
    }

    /**
     * Handle API exceptions
     */
    private function handleApiException(Request $request, Throwable $e): JsonResponse
    {
        $this->logException($e, $request);

        // Handle specific exception types
        if ($e instanceof ValidationException) {
            return $this->handleValidationException($e);
        }

        if ($e instanceof AuthenticationException) {
            return $this->handleAuthenticationException($e);
        }

        if ($e instanceof AuthorizationException) {
            return $this->handleAuthorizationException($e);
        }

        if ($e instanceof ModelNotFoundException) {
            return $this->handleModelNotFoundException($e);
        }

        if ($e instanceof QueryException) {
            return $this->handleQueryException($e);
        }

        if ($e instanceof HttpException) {
            return $this->handleHttpException($e);
        }

        if ($e instanceof NotFoundHttpException) {
            return $this->handleNotFoundHttpException($e);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->handleMethodNotAllowedHttpException($e);
        }

        // Handle generic exceptions
        return $this->handleGenericException($e);
    }

    /**
     * Handle web exceptions
     */
    private function handleWebException(Request $request, Throwable $e): \Illuminate\Http\Response
    {
        $this->logException($e, $request);

        // Handle specific exception types for web
        if ($e instanceof ValidationException) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        if ($e instanceof AuthenticationException) {
            return redirect()->guest(route('login'));
        }

        if ($e instanceof AuthorizationException) {
            return response()->view('errors.403', [], 403);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->view('errors.404', [], 404);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->view('errors.404', [], 404);
        }

        // Handle generic exceptions for web
        if (config('app.debug')) {
            return parent::render($request, $e);
        }

        return response()->view('errors.500', [], 500);
    }

    /**
     * Handle validation exceptions
     */
    private function handleValidationException(ValidationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
            'error_code' => 'VALIDATION_ERROR',
        ], 422);
    }

    /**
     * Handle authentication exceptions
     */
    private function handleAuthenticationException(AuthenticationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Authentication required',
            'error_code' => 'AUTHENTICATION_REQUIRED',
        ], 401);
    }

    /**
     * Handle authorization exceptions
     */
    private function handleAuthorizationException(AuthorizationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Access denied',
            'error_code' => 'AUTHORIZATION_DENIED',
        ], 403);
    }

    /**
     * Handle model not found exceptions
     */
    private function handleModelNotFoundException(ModelNotFoundException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Resource not found',
            'error_code' => 'RESOURCE_NOT_FOUND',
        ], 404);
    }

    /**
     * Handle query exceptions
     */
    private function handleQueryException(QueryException $e): JsonResponse
    {
        // Log the actual database error for debugging
        Log::error('Database query error', [
            'error' => $e->getMessage(),
            'sql' => $e->getSql(),
            'bindings' => $e->getBindings(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Database error occurred',
            'error_code' => 'DATABASE_ERROR',
        ], 500);
    }

    /**
     * Handle HTTP exceptions
     */
    private function handleHttpException(HttpException $e): JsonResponse
    {
        $statusCode = $e->getStatusCode();
        $message = $e->getMessage() ?: $this->getHttpStatusMessage($statusCode);

        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'HTTP_ERROR',
            'status_code' => $statusCode,
        ], $statusCode);
    }

    /**
     * Handle not found HTTP exceptions
     */
    private function handleNotFoundHttpException(NotFoundHttpException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Endpoint not found',
            'error_code' => 'ENDPOINT_NOT_FOUND',
        ], 404);
    }

    /**
     * Handle method not allowed HTTP exceptions
     */
    private function handleMethodNotAllowedHttpException(MethodNotAllowedHttpException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Method not allowed',
            'error_code' => 'METHOD_NOT_ALLOWED',
            'allowed_methods' => $e->getHeaders()['Allow'] ?? [],
        ], 405);
    }

    /**
     * Handle generic exceptions
     */
    private function handleGenericException(Throwable $e): JsonResponse
    {
        $statusCode = 500;
        $message = 'Internal server error';
        $errorCode = 'INTERNAL_ERROR';

        // Check if it's a critical error that needs immediate attention
        if ($this->isCriticalError($e)) {
            $this->sendCriticalErrorNotification($e);
        }

        // In debug mode, show more details
        if (config('app.debug')) {
            $message = $e->getMessage();
            $errorCode = 'DEBUG_ERROR';
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
            'status_code' => $statusCode,
        ], $statusCode);
    }

    /**
     * Log exception with context
     */
    private function logException(Throwable $e, Request $request): void
    {
        $context = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'request' => [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => auth()->id(),
            ],
        ];

        if ($this->isCriticalError($e)) {
            Log::critical('Critical error occurred', $context);
        } else {
            Log::error('Exception occurred', $context);
        }
    }

    /**
     * Check if error is critical
     */
    private function isCriticalError(Throwable $e): bool
    {
        $criticalErrors = [
            'PDOException',
            'RedisException',
            'MemcachedException',
            'GuzzleHttp\Exception\ConnectException',
            'Illuminate\Database\QueryException',
        ];

        return in_array(get_class($e), $criticalErrors) || $e->getCode() >= 500;
    }

    /**
     * Send critical error notification
     */
    private function sendCriticalErrorNotification(Throwable $e): void
    {
        try {
            $adminEmails = config('app.admin_emails', []);
            
            if (!empty($adminEmails)) {
                Mail::raw(
                    "Critical error occurred in COPRRA application:\n\n" .
                    "Error: " . $e->getMessage() . "\n" .
                    "File: " . $e->getFile() . ":" . $e->getLine() . "\n" .
                    "Time: " . now()->toISOString() . "\n" .
                    "URL: " . request()->fullUrl(),
                    function ($message) use ($adminEmails) {
                        $message->to($adminEmails)
                            ->subject('Critical Error Alert - COPRRA');
                    }
                );
            }
        } catch (Throwable $mailException) {
            Log::error('Failed to send critical error notification', [
                'original_error' => $e->getMessage(),
                'mail_error' => $mailException->getMessage(),
            ]);
        }
    }

    /**
     * Get HTTP status message
     */
    private function getHttpStatusMessage(int $statusCode): string
    {
        $messages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
        ];

        return $messages[$statusCode] ?? 'Unknown Error';
    }
}
