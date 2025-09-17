<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileCleanupService
{
    /**
     * @var array<string, mixed>
     */
    private array $config = [];

    public function __construct()
    {
        $config = config('file_cleanup', [
            'temp_files_retention_days' => 7,
            'log_files_retention_days' => 30,
            'cache_files_retention_days' => 14,
            'backup_files_retention_days' => 90,
            'max_storage_size_mb' => 1024, // 1GB
            'cleanup_schedule' => 'daily',
        ]);
        $this->config = is_array($config) ? $config : [];
    }

    /**
     * Clean up temporary files.
     */
    /**
     * @return array<string, mixed>
     */
    public function cleanupTempFiles(): array
    {
        $results = [
            'temp_files' => 0,
            'deleted_size' => 0,
            'errors' => [],
        ];

        try {
            $tempDirectories = [
                storage_path('app/temp'),
                storage_path('app/tmp'),
                storage_path('framework/cache'),
                storage_path('framework/sessions'),
                storage_path('framework/views'),
            ];

            foreach ($tempDirectories as $directory) {
                if (is_dir($directory)) {
                    $retentionDaysValue = $this->config['temp_files_retention_days'] ?? 7;
                    $retentionDays = is_numeric($retentionDaysValue) ? (int) $retentionDaysValue : 7;
                    $cleanupResult = $this->cleanupDirectory($directory, $retentionDays);
                    $results['temp_files'] += $cleanupResult['files_deleted'];
                    $results['deleted_size'] += $cleanupResult['size_deleted'];
                }
            }

            Log::info('Temp files cleanup completed', $results);
        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            Log::error('Temp files cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $results;
    }

    /**
     * Clean up log files.
     */
    /**
     * @return array<string, mixed>
     */
    public function cleanupLogFiles(): array
    {
        $results = [
            'log_files' => 0,
            'deleted_size' => 0,
            'errors' => [],
        ];

        try {
            $logDirectory = storage_path('logs');
            $retentionDaysValue = $this->config['log_files_retention_days'] ?? 30;
            $retentionDays = is_numeric($retentionDaysValue) ? (int) $retentionDaysValue : 30;
            $cutoffDate = Carbon::now()->subDays($retentionDays);

            if (is_dir($logDirectory)) {
                $files = glob($logDirectory.'/*.log');
                if ($files === false) {
                    $files = [];
                }

                foreach ($files as $file) {
                    if (filemtime($file) < $cutoffDate->timestamp) {
                        $size = filesize($file);
                        if (unlink($file)) {
                            $results['log_files']++;
                            $results['deleted_size'] += $size;
                        }
                    }
                }
            }

            Log::info('Log files cleanup completed', $results);
        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            Log::error('Log files cleanup failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }

    /**
     * Clean up cache files.
     */
    /**
     * @return array<string, mixed>
     */
    public function cleanupCacheFiles(): array
    {
        $results = [
            'cache_files' => 0,
            'deleted_size' => 0,
            'errors' => [],
        ];

        try {
            $cacheDirectories = [
                storage_path('framework/cache'),
                storage_path('framework/views'),
                storage_path('framework/sessions'),
            ];

            foreach ($cacheDirectories as $directory) {
                if (is_dir($directory)) {
                    $retentionDaysValue = $this->config['cache_files_retention_days'] ?? 14;
                    $retentionDays = is_numeric($retentionDaysValue) ? (int) $retentionDaysValue : 14;
                    $cleanupResult = $this->cleanupDirectory($directory, $retentionDays);
                    $results['cache_files'] += $cleanupResult['files_deleted'];
                    $results['deleted_size'] += $cleanupResult['size_deleted'];
                }
            }

            // Clear Laravel cache
            \Artisan::call('cache:clear');
            \Artisan::call('view:clear');
            \Artisan::call('config:clear');

            Log::info('Cache files cleanup completed', $results);
        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            Log::error('Cache files cleanup failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }

    /**
     * Clean up backup files.
     */
    /**
     * @return array<string, mixed>
     */
    public function cleanupBackupFiles(): array
    {
        $results = [
            'backup_files' => 0,
            'deleted_size' => 0,
            'errors' => [],
        ];

        try {
            $backupDirectory = storage_path('backups');
            $retentionDaysValue = $this->config['backup_files_retention_days'] ?? 90;
            $retentionDays = is_numeric($retentionDaysValue) ? (int) $retentionDaysValue : 90;
            $cutoffDate = Carbon::now()->subDays($retentionDays);

            if (is_dir($backupDirectory)) {
                $cleanupResult = $this->cleanupDirectory($backupDirectory, $retentionDays);
                $results['backup_files'] = $cleanupResult['files_deleted'];
                $results['deleted_size'] = $cleanupResult['size_deleted'];
            }

            Log::info('Backup files cleanup completed', $results);
        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            Log::error('Backup files cleanup failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }

    /**
     * Clean up uploaded files.
     */
    /**
     * @return array<string, mixed>
     */
    public function cleanupUploadedFiles(): array
    {
        $results = [
            'uploaded_files' => 0,
            'deleted_size' => 0,
            'errors' => [],
        ];

        try {
            $uploadDirectories = [
                storage_path('app/public/uploads'),
                public_path('uploads'),
            ];

            foreach ($uploadDirectories as $directory) {
                if (is_dir($directory)) {
                    $cleanupResult = $this->cleanupDirectory($directory, 30); // 30 days for uploads
                    $results['uploaded_files'] += $cleanupResult['files_deleted'];
                    $results['deleted_size'] += $cleanupResult['size_deleted'];
                }
            }

            Log::info('Uploaded files cleanup completed', $results);
        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            Log::error('Uploaded files cleanup failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }

    /**
     * Perform complete cleanup.
     */
    /**
     * @return array<string, mixed>
     */
    public function performCompleteCleanup(): array
    {
        $results = [
            'temp_files' => $this->cleanupTempFiles(),
            'log_files' => $this->cleanupLogFiles(),
            'cache_files' => $this->cleanupCacheFiles(),
            'backup_files' => $this->cleanupBackupFiles(),
            'uploaded_files' => $this->cleanupUploadedFiles(),
            'total_files_deleted' => 0,
            'total_size_deleted' => 0,
        ];

        // Calculate totals
        foreach ($results as $value) {
            if (is_array($value) && isset($value['files_deleted']) && is_numeric($value['files_deleted'])) {
                $results['total_files_deleted'] += (int) $value['files_deleted'];
            }
            if (is_array($value) && isset($value['deleted_size']) && is_numeric($value['deleted_size'])) {
                $results['total_size_deleted'] += (float) $value['deleted_size'];
            }
        }

        Log::info('Complete file cleanup performed', $results);

        return $results;
    }

    /**
     * Check storage usage.
     */
    /**
     * @return array<string, mixed>
     */
    public function checkStorageUsage(): array
    {
        $storagePath = storage_path();
        $totalSize = $this->getDirectorySize($storagePath);
        $maxSizeValue = $this->config['max_storage_size_mb'] ?? 1024;
        $maxSizeMb = is_numeric($maxSizeValue) ? (float) $maxSizeValue : 1024.0;
        $maxSize = $maxSizeMb * 1024 * 1024; // Convert to bytes

        return [
            'current_size_mb' => round($totalSize / 1024 / 1024, 2),
            'max_size_mb' => $maxSizeMb,
            'usage_percentage' => round(($totalSize / $maxSize) * 100, 2),
            'needs_cleanup' => $totalSize > $maxSize,
        ];
    }

    /**
     * Clean up directory based on age.
     */
    /**
     * @return array<string, int>
     */
    private function cleanupDirectory(string $directory, int $retentionDays): array
    {
        $filesDeleted = 0;
        $sizeDeleted = 0;
        $cutoffDate = Carbon::now()->subDays($retentionDays);

        if (! is_dir($directory)) {
            return ['files_deleted' => 0, 'size_deleted' => 0];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && $file->getMTime() < $cutoffDate->timestamp) {
                $size = $file->getSize();
                if (unlink($file->getPathname())) {
                    $filesDeleted++;
                    $sizeDeleted += $size;
                }
            }
        }

        return [
            'files_deleted' => $filesDeleted,
            'size_deleted' => $sizeDeleted,
        ];
    }

    /**
     * Get directory size.
     */
    private function getDirectorySize(string $directory): int
    {
        $size = 0;

        if (! is_dir($directory)) {
            return $size;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Get cleanup statistics.
     */
    /**
     * @return array<string, mixed>
     */
    public function getCleanupStatistics(): array
    {
        $storageUsage = $this->checkStorageUsage();

        return [
            'storage_usage' => $storageUsage,
            'config' => $this->config,
            'last_cleanup' => $this->getLastCleanupTime(),
            'next_cleanup' => $this->getNextCleanupTime(),
        ];
    }

    /**
     * Get last cleanup time.
     */
    private function getLastCleanupTime(): ?string
    {
        $lastCleanupFile = storage_path('logs/last_cleanup.log');

        if (file_exists($lastCleanupFile)) {
            $content = file_get_contents($lastCleanupFile);

            return $content !== false ? $content : null;
        }

        return null;
    }

    /**
     * Get next cleanup time.
     */
    private function getNextCleanupTime(): string
    {
        $lastCleanup = $this->getLastCleanupTime();

        if ($lastCleanup !== null) {
            try {
                $lastCleanupDate = Carbon::parse($lastCleanup);

                return $lastCleanupDate->addDay()->toISOString();
            } catch (\Exception) {
                // If parsing fails, fall back to default
                return Carbon::now()->addDay()->toISOString();
            }
        }

        return Carbon::now()->addDay()->toISOString();
    }

    /**
     * Schedule cleanup.
     */
    public function scheduleCleanup(): void
    {
        $schedule = $this->config['cleanup_schedule'];

        switch ($schedule) {
            case 'hourly':
            case 'daily':
                \Artisan::call('schedule:run');
                break;
            case 'weekly':
                if (Carbon::now()->isSunday()) {
                    \Artisan::call('schedule:run');
                }
                break;
        }
    }
}
