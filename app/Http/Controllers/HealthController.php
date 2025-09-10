<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $status = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
            'environment' => config('app.env'),
        ];

        try {
            DB::connection()->getPdo();
            $status['database'] = 'connected';
        } catch (\Exception) {
            $status['database'] = 'disconnected';
            $status['status'] = 'unhealthy';
        }

        try {
            Cache::put('health_check', 'ok', 60);
            $status['cache'] = Cache::get('health_check') === 'ok' ? 'working' : 'not_working';
        } catch (\Exception) {
            $status['cache'] = 'not_working';
            $status['status'] = 'unhealthy';
        }

        $status['storage'] = is_writable(storage_path()) ? 'writable' : 'not_writable';

        $httpStatus = $status['status'] === 'healthy' ? 200 : 503;

        return response()->json($status, $httpStatus);
    }
}
