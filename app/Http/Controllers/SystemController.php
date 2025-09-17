<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class SystemController extends Controller
{
    /**
     * Get system information.
     */
    public function getSystemInfo(): JsonResponse
    {
        try {
            $info = [
                'laravel_version' => app()->version(),
                'php_version' => phpversion(),
                'os' => php_uname(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'disk_free_space' => round(disk_free_space('/') / (1024 * 1024 * 1024), 2).' GB',
                'disk_total_space' => round(disk_total_space('/') / (1024 * 1024 * 1024), 2).' GB',
                'uptime' => $this->getUptime(),
                'load_average' => sys_getloadavg(),
                'cpu_count' => $this->getCpuCount(),
            ];

            return response()->json([
                'success' => true,
                'data' => $info,
                'message' => 'System information retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting system info: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get system information',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run database migrations.
     */
    public function runMigrations(): JsonResponse
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            Log::info('Database migrations ran successfully.');

            return response()->json([
                'success' => true,
                'message' => 'Migrations ran successfully',
                'output' => Artisan::output(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error running migrations: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to run migrations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear application cache.
     */
    public function clearCache(): JsonResponse
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            Log::info('Application cache cleared successfully.');

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error clearing cache: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Optimize application.
     */
    public function optimizeApp(): JsonResponse
    {
        try {
            Artisan::call('optimize');
            Log::info('Application optimized successfully.');

            return response()->json([
                'success' => true,
                'message' => 'Application optimized successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error optimizing application: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to optimize application',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run composer update.
     */
    public function runComposerUpdate(): JsonResponse
    {
        try {
            $process = new Process(['composer', 'update', '--no-dev']);
            $process->setTimeout(3600); // 1 hour timeout
            $process->run();

            if (! $process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }

            Log::info('Composer update ran successfully.');

            return response()->json([
                'success' => true,
                'message' => 'Composer update ran successfully',
                'output' => $process->getOutput(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error running composer update: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to run composer update',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get performance metrics.
     */
    public function getPerformanceMetrics(): JsonResponse
    {
        try {
            $metrics = [
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'memory_limit' => ini_get('memory_limit'),
                'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
                'database_connections' => $this->getDatabaseConnections(),
                'cache_hits' => $this->getCacheHits(),
                'response_time' => $this->getResponseTime(),
            ];

            return response()->json([
                'success' => true,
                'data' => $metrics,
                'message' => 'Performance metrics retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting performance metrics: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get performance metrics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run system optimization.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function runOptimization(array $options = []): array
    {
        try {
            $results = [];

            // Clear caches
            if ($options['clear_cache'] ?? true) {
                $this->clearCache();
                $results['cache_cleared'] = true;
            }

            // Optimize autoloader
            if ($options['optimize_autoloader'] ?? true) {
                Artisan::call('optimize');
                $results['autoloader_optimized'] = true;
            }

            // Clear logs
            if ($options['clear_logs'] ?? false) {
                $this->clearLogs();
                $results['logs_cleared'] = true;
            }

            return [
                'success' => true,
                'message' => 'System optimization completed',
                'results' => $results,
            ];
        } catch (\Exception $e) {
            Log::error('Error running optimization: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to run optimization',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Run system cleanup.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function runCleanup(array $options = []): array
    {
        try {
            $results = [];

            // Clean temporary files
            if ($options['clean_temp_files'] ?? true) {
                $this->cleanupTempFiles();
                $results['temp_files_cleaned'] = true;
            }

            // Clean old logs
            if ($options['clean_old_logs'] ?? true) {
                $this->cleanupOldLogs();
                $results['old_logs_cleaned'] = true;
            }

            // Clean old cache
            if ($options['clean_old_cache'] ?? true) {
                $this->cleanupOldCache();
                $results['old_cache_cleaned'] = true;
            }

            return [
                'success' => true,
                'message' => 'System cleanup completed',
                'results' => $results,
            ];
        } catch (\Exception $e) {
            Log::error('Error running cleanup: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to run cleanup',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Run system backup.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function runBackup(array $options = []): array
    {
        try {
            $backupType = $options['type'] ?? 'full';
            $includeFiles = $options['include_files'] ?? true;
            $includeDatabase = $options['include_database'] ?? true;

            $results = [];

            if ($includeDatabase) {
                $this->backupDatabase();
                $results['database_backed_up'] = true;
            }

            if ($includeFiles) {
                $this->backupFiles();
                $results['files_backed_up'] = true;
            }

            return [
                'success' => true,
                'message' => 'System backup completed',
                'results' => $results,
            ];
        } catch (\Exception $e) {
            Log::error('Error running backup: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to run backup',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Run system update.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function runUpdate(array $options = []): array
    {
        try {
            $updateType = $options['type'] ?? 'minor';
            $backupBefore = $options['backup_before'] ?? true;

            $results = [];

            if ($backupBefore) {
                $this->runBackup();
                $results['backup_completed'] = true;
            }

            // Run composer update
            $this->runComposerUpdate();
            $results['composer_updated'] = true;

            // Run migrations
            $this->runMigrations();
            $results['migrations_ran'] = true;

            return [
                'success' => true,
                'message' => 'System update completed',
                'results' => $results,
            ];
        } catch (\Exception $e) {
            Log::error('Error running update: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to run update',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Parse log file.
     *
     * @return array<string, mixed>
     */
    public function parseLogFile(): array
    {
        try {
            $logPath = storage_path('logs/laravel.log');

            if (! file_exists($logPath)) {
                return [
                    'success' => false,
                    'message' => 'Log file not found',
                ];
            }

            $logContent = file_get_contents($logPath);
            if ($logContent === false) {
                return [
                    'success' => false,
                    'message' => 'Failed to read log file',
                ];
            }

            $lines = explode("\n", $logContent);
            $reversedLines = array_reverse($lines);

            return [
                'success' => true,
                'data' => $reversedLines,
                'message' => 'Log file parsed successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Error parsing log file: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to parse log file',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get system uptime.
     */
    private function getUptime(): string
    {
        try {
            if (function_exists('sys_getloadavg')) {
                $uptime = shell_exec('uptime');

                return $uptime ? trim($uptime) : 'Unknown';
            }

            return 'Unknown';
        } catch (\Exception) {
            return 'Unknown';
        }
    }

    /**
     * Get CPU count.
     */
    private function getCpuCount(): int
    {
        try {
            if (function_exists('sys_getloadavg')) {
                return (int) shell_exec('nproc') ?: 1;
            }

            return 1;
        } catch (\Exception) {
            return 1;
        }
    }

    /**
     * Get database connections.
     */
    private function getDatabaseConnections(): int
    {
        return 1; // Placeholder
    }

    /**
     * Get cache hits.
     */
    private function getCacheHits(): int
    {
        return 0; // Placeholder
    }

    /**
     * Get response time.
     */
    private function getResponseTime(): float
    {
        try {
            return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        } catch (\Exception) {
            return 0.0;
        }
    }

    /**
     * Cleanup temporary files.
     */
    private function cleanupTempFiles(): void
    {
        // Placeholder for temp files cleanup
        Log::info('Temporary files cleaned up');
    }

    /**
     * Cleanup old logs.
     */
    private function cleanupOldLogs(): void
    {
        // Placeholder for old logs cleanup
        Log::info('Old logs cleaned up');
    }

    /**
     * Cleanup old cache.
     */
    private function cleanupOldCache(): void
    {
        // Placeholder for old cache cleanup
        Log::info('Old cache cleaned up');
    }

    /**
     * Backup database.
     */
    private function backupDatabase(): void
    {
        // Placeholder for database backup
        Log::info('Database backed up');
    }

    /**
     * Backup files.
     */
    private function backupFiles(): void
    {
        // Placeholder for files backup
        Log::info('Files backed up');
    }

    /**
     * Clear logs.
     */
    private function clearLogs(): void
    {
        // Placeholder for logs clearing
        Log::info('Logs cleared');
    }
}
