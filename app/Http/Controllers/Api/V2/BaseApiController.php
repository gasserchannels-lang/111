<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseApiController as V1BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="COPRRA API v2",
 *     version="2.0.0",
 *     description="Enhanced API for COPRRA - Price Comparison Platform v2",
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
 *     url="https://api.coprra.com/v2",
 *     description="Production Server v2"
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api/v2",
 *     description="Development Server v2"
 * )
 */
abstract class BaseApiController extends V1BaseController
{
    protected int $perPage = 20; // Increased default per page

    protected int $maxPerPage = 200; // Increased max per page

    /**
     * Enhanced success response with v2 features.
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
            'version' => '2.0',
            'timestamp' => now()->toISOString(),
        ];

        if (! empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Enhanced error response with v2 features.
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
            'version' => '2.0',
            'timestamp' => now()->toISOString(),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if (! empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Enhanced paginated response with v2 features.
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
            'links' => [
                'first' => (is_object($data) && method_exists($data, 'url')) ? $data->url(1) : null,
                'last' => (is_object($data) && method_exists($data, 'url') && method_exists($data, 'lastPage')) ? $data->url($data->lastPage()) : null,
                'prev' => (is_object($data) && method_exists($data, 'previousPageUrl')) ? $data->previousPageUrl() : null,
                'next' => (is_object($data) && method_exists($data, 'nextPageUrl')) ? $data->nextPageUrl() : null,
            ],
        ];

        $response = [
            'success' => true,
            'message' => $message,
            'data' => (is_object($data) && method_exists($data, 'items')) ? $data->items() : [],
            'pagination' => $pagination,
            'version' => '2.0',
            'timestamp' => now()->toISOString(),
        ];

        if (! empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response);
    }

    /**
     * Get API version from request.
     */
    protected function getApiVersion(Request $request): string
    {
        return '2.0';
    }

    /**
     * Check API version compatibility.
     */
    protected function checkApiVersion(Request $request): bool
    {
        return true; // v2 is always compatible with itself
    }

    /**
     * Get rate limit information for v2.
     */
    protected function getRateLimitInfo(): array
    {
        return [
            'limit' => 2000, // Increased limit for v2
            'remaining' => 1999,
            'reset' => now()->addHour()->timestamp,
            'version' => '2.0',
        ];
    }

    /**
     * Enhanced filtering with v2 features.
     */
    protected function getFilteringParams(Request $request): array
    {
        $filters = $request->except(['page', 'per_page', 'sort_by', 'sort_order', 'search', 'include', 'fields']);

        // Remove empty values
        $filters = array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });

        // Add v2 specific filters
        if ($request->has('date_from')) {
            $filters['date_from'] = $request->get('date_from');
        }

        if ($request->has('date_to')) {
            $filters['date_to'] = $request->get('date_to');
        }

        return $filters;
    }

    /**
     * Get include parameters for relationships.
     */
    /**
     * @return array<string, mixed>
     */
    protected function getIncludeParams(Request $request): array
    {
        $include = $request->get('include', '');

        if (empty($include)) {
            return [];
        }

        return is_string($include) ? explode(',', $include) : [];
    }

    /**
     * Get fields parameter for field selection.
     */
    /**
     * @return array<string, mixed>
     */
    protected function getFieldsParams(Request $request): array
    {
        $fields = $request->get('fields', '');

        if (empty($fields)) {
            return [];
        }

        return is_string($fields) ? explode(',', $fields) : [];
    }

    /**
     * Enhanced search with v2 features.
     */
    protected function getSearchParams(Request $request): array
    {
        $search = $request->get('search');
        $searchFields = $request->get('search_fields', []);
        $searchMode = $request->get('search_mode', 'contains'); // contains, exact, starts_with, ends_with

        return [
            'search' => $search,
            'search_fields' => $searchFields,
            'search_mode' => $searchMode,
        ];
    }

    /**
     * Get sorting parameters with v2 enhancements.
     */
    protected function getSortingParams(Request $request): array
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $sortMode = $request->get('sort_mode', 'default'); // default, natural, custom

        // Validate sort order
        if (! in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        return [
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'sort_mode' => $sortMode,
        ];
    }

    /**
     * Enhanced API documentation URL for v2.
     */
    protected function getApiDocumentationUrl(): string
    {
        return url('/api/v2/documentation');
    }

    /**
     * Get API changelog URL for v2.
     */
    protected function getApiChangelogUrl(): string
    {
        return url('/api/v2/changelog');
    }

    /**
     * Get API migration guide URL.
     */
    protected function getApiMigrationGuideUrl(): string
    {
        return url('/api/v2/migration-guide');
    }

    /**
     * Get API deprecation notices.
     */
    /**
     * @return array<string, mixed>
     */
    protected function getApiDeprecationNotices(): array
    {
        return [
            'v1_endpoint' => 'Some v1 endpoints will be deprecated in v3.0',
            'migration_guide' => $this->getApiMigrationGuideUrl(),
        ];
    }

    /**
     * Add deprecation headers to response.
     */
    protected function addDeprecationHeaders(JsonResponse $response): JsonResponse
    {
        $response->headers->set('X-API-Version', '2.0');
        $response->headers->set('X-API-Deprecation-Notice', 'Some features may be deprecated in future versions');

        return $response;
    }

    /**
     * Enhanced logging for v2.
     */
    protected function logApiRequest(Request $request, string $action): void
    {
        parent::logApiRequest($request, $action);

        // Add v2 specific logging
        \Log::info('API v2 Request', [
            'version' => '2.0',
            'include' => $this->getIncludeParams($request),
            'fields' => $this->getFieldsParams($request),
        ]);
    }
}
