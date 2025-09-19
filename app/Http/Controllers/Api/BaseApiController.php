<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Info(
 *     title="COPRRA API",
 *     version="1.0.0",
 *     description="API for COPRRA - Price Comparison Platform",
 *
 *     @OA\Contact(
 *         email="api@coprra.com",
 *         name="COPRRA API Support"
 *     ),
 *
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="https://api.coprra.com",
 *     description="Production Server"
 * )
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Development Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="apiKey",
 *     type="apiKey",
 *     in="header",
 *     name="X-API-Key"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and authorization"
 * )
 * @OA\Tag(
 *     name="Products",
 *     description="Product management and search"
 * )
 * @OA\Tag(
 *     name="Categories",
 *     description="Product categories"
 * )
 * @OA\Tag(
 *     name="Brands",
 *     description="Product brands"
 * )
 * @OA\Tag(
 *     name="Stores",
 *     description="Store management"
 * )
 * @OA\Tag(
 *     name="Reviews",
 *     description="Product reviews"
 * )
 * @OA\Tag(
 *     name="Wishlist",
 *     description="User wishlist management"
 * )
 * @OA\Tag(
 *     name="Price Alerts",
 *     description="Price alert management"
 * )
 * @OA\Tag(
 *     name="Statistics",
 *     description="Platform statistics and analytics"
 * )
 * @OA\Tag(
 *     name="Reports",
 *     description="Report generation"
 * )
 */
abstract class BaseApiController extends Controller
{
    protected int $perPage = 15;

    protected int $maxPerPage = 100;

    /**
     * Success response.
     *
     * @param  array<string, mixed>  $meta
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== []) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Error response.
     *
     * @param  array<string, mixed>  $meta
     */
    protected function errorResponse(
        string $message = 'Error',
        int $statusCode = 400,
        mixed $errors = null,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if ($meta !== []) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error response.
     *
     * @param  array<string, mixed>  $errors
     */
    protected function validationErrorResponse(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Not found response.
     */
    protected function notFoundResponse(
        string $message = 'Resource not found'
    ): JsonResponse {
        return $this->errorResponse($message, 404);
    }

    /**
     * Unauthorized response.
     */
    protected function unauthorizedResponse(
        string $message = 'Unauthorized'
    ): JsonResponse {
        return $this->errorResponse($message, 401);
    }

    /**
     * Forbidden response.
     */
    protected function forbiddenResponse(
        string $message = 'Forbidden'
    ): JsonResponse {
        return $this->errorResponse($message, 403);
    }

    /**
     * Rate limit exceeded response.
     */
    protected function rateLimitExceededResponse(
        string $message = 'Rate limit exceeded',
        int $retryAfter = 60
    ): JsonResponse {
        return $this->errorResponse($message, 429, null, [
            'retry_after' => $retryAfter,
        ]);
    }

    /**
     * Server error response.
     */
    protected function serverErrorResponse(
        string $message = 'Internal server error'
    ): JsonResponse {
        return $this->errorResponse($message, 500);
    }

    /**
     * Paginated response.
     *
     * @param  array<string, mixed>  $meta
     */
    protected function paginatedResponse(
        mixed $data,
        string $message = 'Success',
        array $meta = []
    ): JsonResponse {
        $pagination = [
            'current_page' => (is_object($data) && method_exists($data, 'currentPage')) ? $data->currentPage() : 1,
            'per_page' => (is_object($data) && method_exists($data, 'perPage')) ? $data->perPage() : 15,
            'total' => (is_object($data) && method_exists($data, 'total')) ? $data->total() : 0,
            'last_page' => (is_object($data) && method_exists($data, 'lastPage')) ? $data->lastPage() : 1,
            'from' => (is_object($data) && method_exists($data, 'firstItem')) ? $data->firstItem() : null,
            'to' => (is_object($data) && method_exists($data, 'lastItem')) ? $data->lastItem() : null,
            'has_more_pages' => (is_object($data) && method_exists($data, 'hasMorePages')) ? $data->hasMorePages() : false,
        ];

        $response = [
            'success' => true,
            'message' => $message,
            'data' => (is_object($data) && method_exists($data, 'items')) ? $data->items() : [],
            'pagination' => $pagination,
        ];

        if ($meta !== []) {
            $response['meta'] = $meta;
        }

        return response()->json($response);
    }

    /**
     * Validate request data.
     *
     * @param  array<string, mixed>  $rules
     * @param  array<string, mixed>  $messages
     * @return array<string, mixed>
     */
    protected function validateRequest(
        Request $request,
        array $rules,
        array $messages = []
    ): array {
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Get pagination parameters.
     *
     * @return array<string, mixed>
     */
    protected function getPaginationParams(Request $request): array
    {
        $page = max(1, is_numeric($request->get('page', 1)) ? (int) $request->get('page', 1) : 1);
        $perPage = min(
            $this->maxPerPage,
            max(1, is_numeric($request->get('per_page', $this->perPage)) ? (int) $request->get('per_page', $this->perPage) : $this->perPage)
        );

        return [
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * Get sorting parameters.
     *
     * @return array<string, mixed>
     */
    protected function getSortingParams(Request $request): array
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort order
        if (! in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        return [
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ];
    }

    /**
     * Get filtering parameters.
     */
    /**
     * @return array<string, mixed>
     */
    protected function getFilteringParams(Request $request): array
    {
        $filters = $request->except(['page', 'per_page', 'sort_by', 'sort_order', 'search']);

        // Remove empty values
        return array_filter($filters, fn ($value): bool => $value !== null && $value !== '');
    }

    /**
     * Get search parameters.
     */
    /**
     * @return array<string, mixed>
     */
    protected function getSearchParams(Request $request): array
    {
        $search = $request->get('search');
        $searchFields = $request->get('search_fields', []);

        return [
            'search' => $search,
            'search_fields' => $searchFields,
        ];
    }

    /**
     * Handle API exceptions.
     */
    protected function handleApiException(\Throwable $e): JsonResponse
    {
        Log::error('API Exception', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => request()->all(),
        ]);

        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return $this->validationErrorResponse($e->errors());
        }

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            return $this->unauthorizedResponse('Authentication required');
        }

        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return $this->forbiddenResponse('Access denied');
        }

        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFoundResponse('Resource not found');
        }

        // Default server error
        return $this->serverErrorResponse('An error occurred while processing your request');
    }

    /**
     * Log API request.
     */
    protected function logApiRequest(Request $request, string $action): void
    {
        Log::info('API Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'action' => $action,
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'parameters' => $request->except(['password', 'token', 'api_key']),
        ]);
    }

    /**
     * Log API response.
     */
    protected function logApiResponse(JsonResponse $response, string $action): void
    {
        $responseData = $response->getData();
        Log::info('API Response', [
            'action' => $action,
            'status_code' => $response->getStatusCode(),
            'success' => is_object($responseData) && property_exists($responseData, 'success') ? $responseData->success : false,
        ]);
    }

    /**
     * Get API version from request.
     */
    protected function getApiVersion(Request $request): string
    {
        return $request->header('API-Version', '1.0');
    }

    /**
     * Check API version compatibility.
     */
    protected function checkApiVersion(Request $request): bool
    {
        $version = $this->getApiVersion($request);
        $supportedVersions = ['1.0', '1.1'];

        return in_array($version, $supportedVersions);
    }

    /**
     * Get rate limit information.
     */
    /**
     * @return array<string, mixed>
     */
    protected function getRateLimitInfo(): array
    {
        return [
            'limit' => 1000,
            'remaining' => 999,
            'reset' => now()->addHour()->timestamp,
        ];
    }

    /**
     * Add rate limit headers to response.
     */
    protected function addRateLimitHeaders(JsonResponse $response): JsonResponse
    {
        $rateLimitInfo = $this->getRateLimitInfo();

        $response->headers->set('X-RateLimit-Limit', (string) (is_numeric($rateLimitInfo['limit'] ?? null) ? $rateLimitInfo['limit'] : 0));
        $response->headers->set('X-RateLimit-Remaining', (string) (is_numeric($rateLimitInfo['remaining'] ?? null) ? $rateLimitInfo['remaining'] : 0));
        $response->headers->set('X-RateLimit-Reset', (string) (is_numeric($rateLimitInfo['reset'] ?? null) ? $rateLimitInfo['reset'] : 0));

        return $response;
    }

    /**
     * Get API documentation URL.
     */
    protected function getApiDocumentationUrl(): string
    {
        return url('/api/documentation');
    }

    /**
     * Get API changelog URL.
     */
    protected function getApiChangelogUrl(): string
    {
        return url('/api/changelog');
    }

    /**
     * Get API support URL.
     */
    protected function getApiSupportUrl(): string
    {
        return 'mailto:api@coprra.com';
    }
}
