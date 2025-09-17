<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FileSecurityService;
use App\Services\LoginAttemptService;
use App\Services\UserBanService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly LoginAttemptService $loginAttemptService,
        private readonly UserBanService $userBanService,
        private readonly FileSecurityService $fileSecurityService
    ) {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Show admin dashboard.
     */
    public function index(): View
    {
        $statistics = $this->getDashboardStatistics();

        return view('admin.dashboard', ['statistics' => $statistics]);
    }

    /**
     * Get dashboard statistics.
     */
    public function getStatistics(): JsonResponse
    {
        $statistics = $this->getDashboardStatistics();

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Get real-time statistics.
     */
    public function getRealTimeStats(): JsonResponse
    {
        $stats = [
            'online_users' => $this->getOnlineUsersCount(),
            'recent_activities' => $this->getRecentActivities(),
            'system_health' => $this->getSystemHealth(),
            'security_alerts' => $this->getSecurityAlerts(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get dashboard statistics.
     *
     * @return array<string, mixed>
     */
    private function getDashboardStatistics(): array
    {
        return [
            'users' => $this->getUserStatistics(),
            'products' => $this->getProductStatistics(),
            'orders' => $this->getOrderStatistics(),
            'revenue' => $this->getRevenueStatistics(),
            'security' => $this->getSecurityStatistics(),
            'system' => $this->getSystemStatistics(),
            'recent_activities' => $this->getRecentActivities(),
            'charts' => $this->getChartData(),
        ];
    }

    /**
     * Get user statistics.
     *
     * @return array<string, mixed>
     */
    private function getUserStatistics(): array
    {
        return [
            'total_users' => \App\Models\User::count(),
            // @phpstan-ignore-next-line
            'active_users' => \App\Models\User::where('is_active', true)->count(),
            'blocked_users' => 0, // Placeholder - no blocked users column
            'verified_users' => \App\Models\User::whereNotNull('email_verified_at')->count(),
            'new_users_today' => \App\Models\User::whereDate('created_at', today())->count(),
            'new_users_this_week' => \App\Models\User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'new_users_this_month' => \App\Models\User::whereMonth('created_at', now()->month)->count(),
        ];
    }

    /**
     * Get product statistics.
     *
     * @return array<string, mixed>
     */
    private function getProductStatistics(): array
    {
        return [
            'total_products' => \App\Models\Product::count(),
            'active_products' => \App\Models\Product::where('is_active', true)->count(),
            // @phpstan-ignore-next-line
            'featured_products' => \App\Models\Product::where('is_featured', true)->count(),
            // @phpstan-ignore-next-line
            'out_of_stock' => \App\Models\Product::where('stock_quantity', 0)->count(),
            // @phpstan-ignore-next-line
            'low_stock' => \App\Models\Product::where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10)->count(),
            'new_products_today' => \App\Models\Product::whereDate('created_at', today())->count(),
        ];
    }

    /**
     * Get order statistics.
     *
     * @return array<string, mixed>
     */
    private function getOrderStatistics(): array
    {
        return [
            'total_orders' => 0, // Placeholder
            'pending_orders' => 0, // Placeholder
            'completed_orders' => 0, // Placeholder
            'cancelled_orders' => 0, // Placeholder
            'orders_today' => 0, // Placeholder
            'orders_this_week' => 0, // Placeholder
            'orders_this_month' => 0, // Placeholder
        ];
    }

    /**
     * Get revenue statistics.
     *
     * @return array<string, mixed>
     */
    private function getRevenueStatistics(): array
    {
        return [
            'total_revenue' => 0, // Placeholder
            'revenue_today' => 0, // Placeholder
            'revenue_this_week' => 0, // Placeholder
            'revenue_this_month' => 0, // Placeholder
            'average_order_value' => 0, // Placeholder
            'revenue_growth' => 0, // Placeholder
        ];
    }

    /**
     * Get security statistics.
     *
     * @return array<string, mixed>
     */
    private function getSecurityStatistics(): array
    {
        return [
            'login_attempts' => $this->loginAttemptService->getStatistics(),
            'banned_users' => $this->userBanService->getBanStatistics(),
            'file_security' => $this->fileSecurityService->getStatistics(),
            'failed_logins_today' => $this->getFailedLoginsToday(),
            'security_incidents' => $this->getSecurityIncidents(),
        ];
    }

    /**
     * Get system statistics.
     *
     * @return array<string, mixed>
     */
    private function getSystemStatistics(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'disk_usage' => $this->getDiskUsage(),
            'database_size' => $this->getDatabaseSize(),
            'cache_status' => $this->getCacheStatus(),
        ];
    }

    /**
     * Get recent activities.
     *
     * @return array<string, mixed>
     */
    private function getRecentActivities(): array
    {
        // Get recent activities from AuditService
        return [];
    }

    /**
     * Get chart data.
     *
     * @return array<string, mixed>
     */
    private function getChartData(): array
    {
        return [
            'user_registrations' => $this->getUserRegistrationChart(),
            'product_views' => $this->getProductViewsChart(),
            'revenue_chart' => $this->getRevenueChart(),
            'security_incidents' => $this->getSecurityIncidentsChart(),
        ];
    }

    /**
     * Get online users count.
     */
    private function getOnlineUsersCount(): int
    {
        // This would require a proper online users tracking system
        return 0;
    }

    /**
     * Get system health.
     *
     * @return array<string, mixed>
     */
    private function getSystemHealth(): array
    {
        return [
            'database' => $this->checkDatabaseHealth(),
            'cache' => $this->checkCacheHealth(),
            'storage' => $this->checkStorageHealth(),
            'memory' => $this->checkMemoryHealth(),
        ];
    }

    /**
     * Get security alerts.
     *
     * @return array<string, mixed>
     */
    private function getSecurityAlerts(): array
    {
        return [
            'failed_logins' => $this->getFailedLoginsToday(),
            'blocked_ips' => count($this->loginAttemptService->getBlockedIps()),
            'banned_users' => count($this->userBanService->getBannedUsers()),
            'security_incidents' => $this->getSecurityIncidents(),
        ];
    }

    /**
     * Get failed logins today.
     */
    private function getFailedLoginsToday(): int
    {
        // This would require proper logging implementation
        return 0;
    }

    /**
     * Get security incidents.
     *
     * @return array<string, mixed>
     */
    private function getSecurityIncidents(): array
    {
        // This would require proper security incident tracking
        return [];
    }

    /**
     * Get disk usage.
     *
     * @return array<string, mixed>
     */
    private function getDiskUsage(): array
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;

        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'percentage' => round(($used / $total) * 100, 2),
        ];
    }

    /**
     * Get database size.
     */
    private function getDatabaseSize(): int
    {
        // This would require database size calculation
        return 0;
    }

    /**
     * Get cache status.
     *
     * @return array<string, mixed>
     */
    private function getCacheStatus(): array
    {
        try {
            \Cache::put('test_key', 'test_value', 1);
            $status = \Cache::get('test_key') === 'test_value';
            \Cache::forget('test_key');

            return [
                'status' => $status ? 'working' : 'error',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'driver' => config('cache.default'),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check database health.
     *
     * @return array<string, string>
     */
    private function checkDatabaseHealth(): array
    {
        try {
            \DB::connection()->getPdo();

            return ['status' => 'healthy', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check cache health.
     *
     * @return array<string, string>
     */
    private function checkCacheHealth(): array
    {
        try {
            \Cache::put('health_check', 'ok', 1);
            $result = \Cache::get('health_check');
            \Cache::forget('health_check');

            return ['status' => $result === 'ok' ? 'healthy' : 'error', 'message' => 'Cache test completed'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check storage health.
     *
     * @return array<string, string>
     */
    private function checkStorageHealth(): array
    {
        try {
            $testFile = 'health_check_'.time().'.txt';
            \Storage::put($testFile, 'test');
            $result = \Storage::get($testFile);
            \Storage::delete($testFile);

            return ['status' => $result === 'test' ? 'healthy' : 'error', 'message' => 'Storage test completed'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check memory health.
     *
     * @return array<string, mixed>
     */
    private function checkMemoryHealth(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->convertToBytes($memoryLimit);
        $percentage = ($memoryUsage / $memoryLimitBytes) * 100;

        return [
            'status' => $percentage > 90 ? 'warning' : 'healthy',
            'usage' => $memoryUsage,
            'limit' => $memoryLimitBytes,
            'percentage' => round($percentage, 2),
        ];
    }

    /**
     * Convert memory limit to bytes.
     */
    private function convertToBytes(string $memoryLimit): int
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $memoryLimit = (int) $memoryLimit;

        switch ($last) {
            case 'g':
                $memoryLimit *= 1024;
                // no break
            case 'm':
                $memoryLimit *= 1024;
                // no break
            case 'k':
                $memoryLimit *= 1024;
        }

        return $memoryLimit;
    }

    /**
     * Get user registration chart data.
     *
     * @return list<array<string, int|string>>
     */
    private function getUserRegistrationChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'count' => \App\Models\User::whereDate('created_at', $date)->count(),
            ];
        }

        return $data;
    }

    /**
     * Get product views chart data.
     *
     * @return array<string, mixed>
     */
    private function getProductViewsChart(): array
    {
        // This would require product views tracking
        return [];
    }

    /**
     * Get revenue chart data.
     *
     * @return array<string, mixed>
     */
    private function getRevenueChart(): array
    {
        // This would require revenue tracking
        return [];
    }

    /**
     * Get security incidents chart data.
     *
     * @return array<string, mixed>
     */
    private function getSecurityIncidentsChart(): array
    {
        // This would require security incident tracking
        return [];
    }
}
