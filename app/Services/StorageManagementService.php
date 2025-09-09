<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class StorageManagementService
{
    private array $config;
    private FileCleanupService $cleanupService;

    public function __construct(FileCleanupService $cleanupService)
    {
        $this->cleanupService = $cleanupService;
        $this->config = config('storage_management', [
            'max_storage_size_mb' => 1024, // 1GB
            'warning_threshold' => 80, // 80%
            'critical_threshold' => 95, // 95%
            'auto_cleanup' => true,
            'cleanup_priority' => ['temp', 'cache', 'logs', 'backups'],
            'compression_enabled' => true,
            'archival_enabled' => true,
        ]);
    }

    /**
     * Monitor storage usage
     */
    public function monitorStorageUsage(): array
    {
        $storagePath = storage_path();
        $totalSize = $this->getDirectorySize($storagePath);
        $maxSize = $this->config['max_storage_size_mb'] * 1024 * 1024;
        $usagePercentage = ($totalSize / $maxSize) * 100;

        $status = 'healthy';
        if ($usagePercentage >= $this->config['critical_threshold']) {
            $status = 'critical';
        } elseif ($usagePercentage >= $this->config['warning_threshold']) {
            $status = 'warning';
        }

        $result = [
            'current_size_mb' => round($totalSize / 1024 / 1024, 2),
            'max_size_mb' => $this->config['max_storage_size_mb'],
            'usage_percentage' => round($usagePercentage, 2),
            'status' => $status,
            'needs_cleanup' => $usagePercentage >= $this->config['warning_threshold'],
            'breakdown' => $this->getStorageBreakdown(),
        ];

        // Log warning or critical status
        if ($status === 'warning') {
            Log::warning('Storage usage warning', $result);
        } elseif ($status === 'critical') {
            Log::error('Storage usage critical', $result);
        }

        return $result;
    }

    /**
     * Get storage breakdown by directory
     */
    public function getStorageBreakdown(): array
    {
        $breakdown = [];
        $directories = [
            'logs' => storage_path('logs'),
            'cache' => storage_path('framework/cache'),
            'sessions' => storage_path('framework/sessions'),
            'views' => storage_path('framework/views'),
            'temp' => storage_path('app/temp'),
            'backups' => storage_path('backups'),
            'uploads' => storage_path('app/public/uploads'),
            'other' => storage_path('app'),
        ];

        foreach ($directories as $name => $path) {
            if (is_dir($path)) {
                $size = $this->getDirectorySize($path);
                $breakdown[$name] = [
                    'size_mb' => round($size / 1024 / 1024, 2),
                    'size_bytes' => $size,
                    'path' => $path,
                ];
            }
        }

        return $breakdown;
    }

    /**
     * Auto cleanup if needed
     */
    public function autoCleanupIfNeeded(): array
    {
        $usage = $this->monitorStorageUsage();
        
        if (!$usage['needs_cleanup'] || !$this->config['auto_cleanup']) {
            return [
                'cleanup_performed' => false,
                'reason' => 'No cleanup needed or auto cleanup disabled',
                'usage' => $usage,
            ];
        }

        $cleanupResults = [];
        $priority = $this->config['cleanup_priority'];

        foreach ($priority as $type) {
            switch ($type) {
                case 'temp':
                    $cleanupResults['temp'] = $this->cleanupService->cleanupTempFiles();
                    break;
                case 'cache':
                    $cleanupResults['cache'] = $this->cleanupService->cleanupCacheFiles();
                    break;
                case 'logs':
                    $cleanupResults['logs'] = $this->cleanupService->cleanupLogFiles();
                    break;
                case 'backups':
                    $cleanupResults['backups'] = $this->cleanupService->cleanupBackupFiles();
                    break;
            }

            // Check if usage is now acceptable
            $newUsage = $this->monitorStorageUsage();
            if ($newUsage['status'] === 'healthy') {
                break;
            }
        }

        return [
            'cleanup_performed' => true,
            'cleanup_results' => $cleanupResults,
            'usage_before' => $usage,
            'usage_after' => $this->monitorStorageUsage(),
        ];
    }

    /**
     * Compress old files
     */
    public function compressOldFiles(): array
    {
        if (!$this->config['compression_enabled']) {
            return ['compression_disabled' => true];
        }

        $results = [
            'files_compressed' => 0,
            'space_saved_mb' => 0,
            'errors' => [],
        ];

        try {
            $directories = [
                storage_path('logs'),
                storage_path('backups'),
            ];

            foreach ($directories as $directory) {
                if (is_dir($directory)) {
                    $compressionResult = $this->compressDirectory($directory);
                    $results['files_compressed'] += $compressionResult['files_compressed'];
                    $results['space_saved_mb'] += $compressionResult['space_saved_mb'];
                }
            }

            Log::info('File compression completed', $results);

        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            Log::error('File compression failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }

    /**
     * Archive old files
     */
    public function archiveOldFiles(): array
    {
        if (!$this->config['archival_enabled']) {
            return ['archival_disabled' => true];
        }

        $results = [
            'files_archived' => 0,
            'archives_created' => 0,
            'space_saved_mb' => 0,
            'errors' => [],
        ];

        try {
            $archiveDirectories = [
                'logs' => storage_path('logs'),
                'backups' => storage_path('backups'),
            ];

            foreach ($archiveDirectories as $name => $directory) {
                if (is_dir($directory)) {
                    $archiveResult = $this->createArchive($directory, $name);
                    $results['files_archived'] += $archiveResult['files_archived'];
                    $results['archives_created'] += $archiveResult['archives_created'];
                    $results['space_saved_mb'] += $archiveResult['space_saved_mb'];
                }
            }

            Log::info('File archival completed', $results);

        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            Log::error('File archival failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }

    /**
     * Get storage recommendations
     */
    public function getStorageRecommendations(): array
    {
        $usage = $this->monitorStorageUsage();
        $breakdown = $usage['breakdown'];
        $recommendations = [];

        // Sort directories by size
        uasort($breakdown, function ($a, $b) {
            return $b['size_mb'] <=> $a['size_mb'];
        });

        // Generate recommendations based on usage
        if ($usage['status'] === 'critical') {
            $recommendations[] = [
                'type' => 'critical',
                'message' => 'Storage usage is critical. Immediate cleanup required.',
                'action' => 'Run complete cleanup immediately',
            ];
        } elseif ($usage['status'] === 'warning') {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Storage usage is high. Consider cleanup.',
                'action' => 'Run cleanup for largest directories',
            ];
        }

        // Directory-specific recommendations
        foreach ($breakdown as $name => $data) {
            if ($data['size_mb'] > 100) { // More than 100MB
                $recommendations[] = [
                    'type' => 'info',
                    'message' => "Directory '{$name}' is using {$data['size_mb']}MB",
                    'action' => "Consider cleaning up {$name} directory",
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Set storage limits
     */
    public function setStorageLimits(array $limits): bool
    {
        try {
            $this->config = array_merge($this->config, $limits);
            
            // Update config file
            $configPath = config_path('storage_management.php');
            $configContent = "<?php\n\nreturn " . var_export($this->config, true) . ";\n";
            file_put_contents($configPath, $configContent);
            
            Log::info('Storage limits updated', $limits);
            
            return true;
            
        } catch (Exception $e) {
            Log::error('Failed to update storage limits', [
                'error' => $e->getMessage(),
                'limits' => $limits,
            ]);
            
            return false;
        }
    }

    /**
     * Get storage statistics
     */
    public function getStorageStatistics(): array
    {
        $usage = $this->monitorStorageUsage();
        $breakdown = $usage['breakdown'];
        
        $totalFiles = 0;
        $oldestFile = null;
        $newestFile = null;

        foreach ($breakdown as $data) {
            $files = $this->getFilesInDirectory($data['path']);
            $totalFiles += count($files);
            
            foreach ($files as $file) {
                $fileTime = filemtime($file);
                if (!$oldestFile || $fileTime < $oldestFile) {
                    $oldestFile = $fileTime;
                }
                if (!$newestFile || $fileTime > $newestFile) {
                    $newestFile = $fileTime;
                }
            }
        }

        return [
            'usage' => $usage,
            'breakdown' => $breakdown,
            'total_files' => $totalFiles,
            'oldest_file' => $oldestFile ? Carbon::createFromTimestamp($oldestFile)->toISOString() : null,
            'newest_file' => $newestFile ? Carbon::createFromTimestamp($newestFile)->toISOString() : null,
            'recommendations' => $this->getStorageRecommendations(),
        ];
    }

    /**
     * Compress directory
     */
    private function compressDirectory(string $directory): array
    {
        $filesCompressed = 0;
        $spaceSaved = 0;

        $files = glob($directory . '/*');
        foreach ($files as $file) {
            if (is_file($file) && !str_ends_with($file, '.gz')) {
                $originalSize = filesize($file);
                $compressedFile = $file . '.gz';
                
                if (file_put_contents($compressedFile, gzencode(file_get_contents($file)))) {
                    $compressedSize = filesize($compressedFile);
                    $spaceSaved += $originalSize - $compressedSize;
                    unlink($file); // Remove original file
                    $filesCompressed++;
                }
            }
        }

        return [
            'files_compressed' => $filesCompressed,
            'space_saved_mb' => round($spaceSaved / 1024 / 1024, 2),
        ];
    }

    /**
     * Create archive
     */
    private function createArchive(string $directory, string $name): array
    {
        $archiveName = $name . '_' . Carbon::now()->format('Y-m-d') . '.tar.gz';
        $archivePath = storage_path('archives/' . $archiveName);
        
        // Create archives directory if it doesn't exist
        if (!is_dir(dirname($archivePath))) {
            mkdir(dirname($archivePath), 0755, true);
        }

        $command = "tar -czf {$archivePath} -C " . dirname($directory) . " " . basename($directory);
        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            $originalSize = $this->getDirectorySize($directory);
            $archiveSize = filesize($archivePath);
            $spaceSaved = $originalSize - $archiveSize;
            
            // Remove original directory
            $this->removeDirectory($directory);
            
            return [
                'files_archived' => count(glob($directory . '/*')),
                'archives_created' => 1,
                'space_saved_mb' => round($spaceSaved / 1024 / 1024, 2),
            ];
        }

        return [
            'files_archived' => 0,
            'archives_created' => 0,
            'space_saved_mb' => 0,
        ];
    }

    /**
     * Get files in directory
     */
    private function getFilesInDirectory(string $directory): array
    {
        $files = [];
        
        if (is_dir($directory)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $files[] = $file->getPathname();
                }
            }
        }
        
        return $files;
    }

    /**
     * Remove directory recursively
     */
    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file);
            } else {
                unlink($file);
            }
        }

        rmdir($directory);
    }

    /**
     * Get directory size
     */
    private function getDirectorySize(string $directory): int
    {
        $size = 0;

        if (!is_dir($directory)) {
            return $size;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }
}
