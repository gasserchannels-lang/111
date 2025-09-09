<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

Route::get('/health', function () {
    $status = [
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
        'environment' => config('app.env'),
    ];

    // Check database connection
    try {
        DB::connection()->getPdo();
        $status['database'] = 'connected';
    } catch (Exception $e) {
        $status['database'] = 'disconnected';
        $status['status'] = 'unhealthy';
    }

    // Check cache
    try {
        Cache::put('health_check', 'ok', 60);
        $status['cache'] = Cache::get('health_check') === 'ok' ? 'working' : 'not_working';
    } catch (Exception $e) {
        $status['cache'] = 'not_working';
        $status['status'] = 'unhealthy';
    }

    // Check storage
    $status['storage'] = is_writable(storage_path()) ? 'writable' : 'not_writable';

    $httpStatus = $status['status'] === 'healthy' ? 200 : 503;
    
    return response()->json($status, $httpStatus);
});
