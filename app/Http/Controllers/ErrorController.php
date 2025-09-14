<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ErrorController extends Controller
{
    /**
     * Display error dashboard.
     */
    public function index(Request $request): View|JsonResponse
    {
        $errors = $this->getRecentErrors();
        $errorStats = $this->getErrorStatistics();
        $systemHealth = $this->getSystemHealth();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'errors' => $errors,
                    'statistics' => $errorStats,
                    'system_health' => $systemHealth,
                ],
            ]);
        }

        /** @var view-string $view */
        $view = 'errors.dashboard';

        return view($view, compact('errors', 'errorStats', 'systemHealth'));
    }

    /**
     * Display error details.
     */
    public function show(Request $request, string $id): View|JsonResponse
    {
        $error = $this->getErrorById($id);

        if (! $error) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error not found',
                ], 404);
            }

            return view('errors.404');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $error,
            ]);
        }

        /** @var view-string $view */
        $view = 'errors.details';

        return view($view, compact('error'));
    }

    /**
     * Get recent errors.
     *
     * @return array<string, mixed>
     */
    /**
     * @return list<array<string, mixed>>
     */
    public function getRecentErrors(int $limit = 50): array
    {
        try {
            // Get errors from log files
            $logFiles = glob(storage_path('logs/*.log'));
            $errors = [];

            foreach ($logFiles ?: [] as $logFile) {
                $content = file_get_contents($logFile);
                $lines = explode("\n", $content ?: '');

                foreach ($lines as $line) {
                    if (strpos($line, 'ERROR') !== false || strpos($line, 'CRITICAL') !== false) {
                        $errors[] = $this->parseLogLine($line);
                    }
                }
            }

            // Sort by timestamp (newest first)
            usort($errors, function ($a, $b) {
                $timestampA = (isset($a['timestamp']) && is_string($a['timestamp'])) ? strtotime($a['timestamp']) : 0;
                $timestampB = (isset($b['timestamp']) && is_string($b['timestamp'])) ? strtotime($b['timestamp']) : 0;

                return (int) $timestampB - (int) $timestampA;
            });

            return array_slice($errors, 0, $limit);
        } catch (\Exception $e) {
            Log::error('Failed to get recent errors', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get error statistics.
     */
    /**
     * @return array<string, mixed>
     */
    public function getErrorStatistics(): array
    {
        try {
            $logFiles = glob(storage_path('logs/*.log'));
            $stats = [
                'total_errors' => 0,
                'critical_errors' => 0,
                'errors_by_type' => [],
                'errors_by_hour' => [],
                'errors_by_day' => [],
            ];

            foreach ($logFiles ?: [] as $logFile) {
                $content = file_get_contents($logFile);
                $lines = explode("\n", $content ?: '');

                foreach ($lines as $line) {
                    if (strpos($line, 'ERROR') !== false || strpos($line, 'CRITICAL') !== false) {
                        $error = $this->parseLogLine($line);

                        $stats['total_errors']++;

                        if (strpos($line, 'CRITICAL') !== false) {
                            $stats['critical_errors']++;
                        }

                        // Count by type
                        $type = is_string($error['type'] ?? '') ? $error['type'] : 'Unknown';
                        if (is_string($type)) {
                            $stats['errors_by_type'][$type] = ($stats['errors_by_type'][$type] ?? 0) + 1;
                        }

                        // Count by hour
                        $timestampValue = $error['timestamp'] ?? null;
                        $timestamp = is_string($timestampValue) ? strtotime($timestampValue) : time();
                        $validTimestamp = $timestamp === false ? time() : $timestamp;
                        $hour = date('H', $validTimestamp);
                        $stats['errors_by_hour'][$hour] = ($stats['errors_by_hour'][$hour] ?? 0) + 1;

                        // Count by day
                        $day = date('Y-m-d', $validTimestamp);
                        $stats['errors_by_day'][$day] = ($stats['errors_by_day'][$day] ?? 0) + 1;
                    }
                }
            }

            return $stats;
        } catch (\Exception $e) {
            Log::error('Failed to get error statistics', [
                'error' => $e->getMessage(),
            ]);

            return [
                'total_errors' => 0,
                'critical_errors' => 0,
                'errors_by_type' => [],
                'errors_by_hour' => [],
                'errors_by_day' => [],
            ];
        }
    }

    /**
     * Get system health.
     */
    /**
     * @return array<string, mixed>
     */
    public function getSystemHealth(): array
    {
        try {
            $health = [
                'database' => $this->checkDatabaseHealth(),
                'cache' => $this->checkCacheHealth(),
                'storage' => $this->checkStorageHealth(),
                'memory' => $this->checkMemoryHealth(),
                'disk_space' => $this->checkDiskSpaceHealth(),
            ];

            $overallHealth = 'healthy';
            foreach ($health as $component => $status) {
                if ($status['status'] === 'critical') {
                    $overallHealth = 'critical';
                    break;
                } elseif ($status['status'] === 'warning') {
                    $overallHealth = 'warning';
                }
            }

            $health['overall'] = $overallHealth;

            return $health;
        } catch (\Exception $e) {
            Log::error('Failed to get system health', [
                'error' => $e->getMessage(),
            ]);

            return [
                'overall' => 'unknown',
                'database' => ['status' => 'unknown'],
                'cache' => ['status' => 'unknown'],
                'storage' => ['status' => 'unknown'],
                'memory' => ['status' => 'unknown'],
                'disk_space' => ['status' => 'unknown'],
            ];
        }
    }

    /**
     * Get error by ID.
     */
    /**
     * @return array<string, mixed>|null
     */
    private function getErrorById(string $id): ?array
    {
        // This would typically query a database or log storage
        // For now, we'll search through log files
        $logFiles = glob(storage_path('logs/*.log'));

        foreach ($logFiles ?: [] as $logFile) {
            $content = file_get_contents($logFile);
            $lines = explode("\n", $content ?: '');

            foreach ($lines as $line) {
                if (strpos($line, $id) !== false) {
                    return $this->parseLogLine($line);
                }
            }
        }

        return null;
    }

    /**
     * Parse log line.
     */
    /**
     * @return array<string, mixed>
     */
    private function parseLogLine(string $line): array
    {
        // Basic log parsing - this would be more sophisticated in production
        $parts = explode(' ', $line, 4);

        return [
            'id' => uniqid(),
            'timestamp' => $parts[0],
            'level' => $parts[1] ?? 'ERROR',
            'type' => $this->extractErrorType($line),
            'message' => $parts[3] ?? $line,
            'context' => $this->extractContext($line),
        ];
    }

    /**
     * Extract error type from log line.
     */
    private function extractErrorType(string $line): string
    {
        if (strpos($line, 'Database') !== false) {
            return 'Database';
        }
        if (strpos($line, 'Redis') !== false) {
            return 'Cache';
        }
        if (strpos($line, 'Validation') !== false) {
            return 'Validation';
        }
        if (strpos($line, 'Authentication') !== false) {
            return 'Authentication';
        }
        if (strpos($line, 'Authorization') !== false) {
            return 'Authorization';
        }

        return 'General';
    }

    /**
     * Extract context from log line.
     */
    /**
     * @return array<string, mixed>
     */
    private function extractContext(string $line): array
    {
        // Extract JSON context if present
        if (preg_match('/\{.*\}/', $line, $matches)) {
            $context = json_decode($matches[0], true);

            return is_array($context) ? $context : [];
        }

        return [];
    }

    /**
     * Check database health.
     */
    /**
     * @return array<string, mixed>
     */
    private function checkDatabaseHealth(): array
    {
        try {
            DB::select('SELECT 1');

            return ['status' => 'healthy', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'critical', 'message' => 'Database connection failed: '.$e->getMessage()];
        }
    }

    /**
     * Check cache health.
     */
    /**
     * @return array<string, mixed>
     */
    private function checkCacheHealth(): array
    {
        try {
            $testKey = 'health_check_'.time();
            \Cache::put($testKey, 'test', 60);
            $retrieved = \Cache::get($testKey);
            \Cache::forget($testKey);

            if ($retrieved === 'test') {
                return ['status' => 'healthy', 'message' => 'Cache is working'];
            }

            return ['status' => 'warning', 'message' => 'Cache test failed'];
        } catch (\Exception $e) {
            return ['status' => 'critical', 'message' => 'Cache error: '.$e->getMessage()];
        }
    }

    /**
     * Check storage health.
     */
    /**
     * @return array<string, mixed>
     */
    private function checkStorageHealth(): array
    {
        try {
            $storagePath = storage_path();
            $totalSpace = disk_total_space($storagePath);
            $freeSpace = disk_free_space($storagePath);
            $usedSpace = $totalSpace - $freeSpace;
            $usagePercentage = ($usedSpace / $totalSpace) * 100;

            if ($usagePercentage > 90) {
                return ['status' => 'critical', 'message' => 'Storage usage critical: '.round($usagePercentage, 2).'%'];
            } elseif ($usagePercentage > 80) {
                return ['status' => 'warning', 'message' => 'Storage usage high: '.round($usagePercentage, 2).'%'];
            }

            return ['status' => 'healthy', 'message' => 'Storage usage normal: '.round($usagePercentage, 2).'%'];
        } catch (\Exception $e) {
            return ['status' => 'critical', 'message' => 'Storage check failed: '.$e->getMessage()];
        }
    }

    /**
     * Check memory health.
     */
    /**
     * @return array<string, mixed>
     */
    private function checkMemoryHealth(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->convertToBytes($memoryLimit);
        $usagePercentage = ($memoryUsage / $memoryLimitBytes) * 100;

        if ($usagePercentage > 90) {
            return ['status' => 'critical', 'message' => 'Memory usage critical: '.round($usagePercentage, 2).'%'];
        } elseif ($usagePercentage > 80) {
            return ['status' => 'warning', 'message' => 'Memory usage high: '.round($usagePercentage, 2).'%'];
        }

        return ['status' => 'healthy', 'message' => 'Memory usage normal: '.round($usagePercentage, 2).'%'];
    }

    /**
     * Check disk space health.
     */
    /**
     * @return array<string, mixed>
     */
    private function checkDiskSpaceHealth(): array
    {
        $diskSpace = disk_free_space(storage_path());
        $diskSpaceGB = $diskSpace / (1024 * 1024 * 1024);

        if ($diskSpaceGB < 1) {
            return ['status' => 'critical', 'message' => 'Disk space critical: '.round($diskSpaceGB, 2).'GB free'];
        } elseif ($diskSpaceGB < 5) {
            return ['status' => 'warning', 'message' => 'Disk space low: '.round($diskSpaceGB, 2).'GB free'];
        }

        return ['status' => 'healthy', 'message' => 'Disk space normal: '.round($diskSpaceGB, 2).'GB free'];
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
}
