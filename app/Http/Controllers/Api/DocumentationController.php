<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     title="COPRRA API Documentation",
 *     version="1.0.0",
 *     description="API documentation for COPRRA Price Comparison Platform",
 *
 *     @OA\Contact(
 *         email="support@coprra.com",
 *         name="COPRRA Support"
 *     ),
 *
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Development Server"
 * )
 * @OA\Server(
 *     url="https://api.coprra.com",
 *     description="Production Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class DocumentationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/",
     *     summary="API Status",
     *     description="Get API status and version information",
     *     operationId="getApiStatus",
     *     tags={"General"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="COPRRA API is running"),
     *             @OA\Property(property="version", type="string", example="1.0.0"),
     *             @OA\Property(property="timestamp", type="string", format="date-time")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'COPRRA API is running',
            'version' => '1.0.0',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/health",
     *     summary="Health Check",
     *     description="Check API health status",
     *     operationId="getHealthStatus",
     *     tags={"General"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="API is healthy",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="healthy"),
     *             @OA\Property(property="database", type="string", example="connected"),
     *             @OA\Property(property="cache", type="string", example="working"),
     *             @OA\Property(property="storage", type="string", example="writable")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=503,
     *         description="API is unhealthy",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="unhealthy"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function health(): JsonResponse
    {
        $status = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
            'environment' => config('app.env'),
        ];

        // Check database connection
        try {
            \DB::connection()->getPdo();
            $status['database'] = 'connected';
        } catch (\Exception $e) {
            $status['database'] = 'disconnected';
            $status['status'] = 'unhealthy';
        }

        // Check cache
        try {
            \Cache::put('health_check', 'ok', 60);
            $status['cache'] = \Cache::get('health_check') === 'ok' ? 'working' : 'not_working';
        } catch (\Exception $e) {
            $status['cache'] = 'not_working';
            $status['status'] = 'unhealthy';
        }

        // Check storage
        $status['storage'] = is_writable(storage_path()) ? 'writable' : 'not_writable';

        $httpStatus = $status['status'] === 'healthy' ? 200 : 503;

        return response()->json($status, $httpStatus);
    }
}
