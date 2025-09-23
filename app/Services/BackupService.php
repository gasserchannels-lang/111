<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class BackupService
{
    private readonly string $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('backups');
    }

    /**
     * Create full backup.
     */
    /**
     * @return array<string, mixed>
     */
    public function createFullBackup(): array
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupName = "full_backup_{$timestamp}";

        try {
            Log::info('Starting full backup', ['backup_name' => $backupName]);

            $results = [
                'backup_name' => $backupName,
                'started_at' => now(),
                'components' => [],
            ];

            // Create backup directory
            $backupDir = $this->backupPath.'/'.$backupName;
            if (! is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // Backup database
            $results['components']['database'] = $this->backupDatabase($backupDir);

            // Backup files
            $results['components']['files'] = $this->backupFiles($backupDir);

            // Backup configuration
            $results['components']['config'] = $this->backupConfiguration($backupDir);

            // Create backup manifest
            $results['components']['manifest'] = $this->createBackupManifest($backupDir, $results);

            // Compress backup
            $results['components']['compression'] = $this->compressBackup($backupDir, $backupName);

            $results['completed_at'] = now();
            $results['status'] = 'completed';
            $results['size'] = $this->getBackupSize($backupDir);

            Log::info('Full backup completed', [
                'backup_name' => $backupName,
                'size' => $results['size'],
                'duration' => $results['completed_at']->diffInSeconds($results['started_at']),
            ]);

            return $results;
        } catch (Exception $e) {
            Log::error('Full backup failed', [
                'backup_name' => $backupName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Create database backup.
     */
    /**
     * @return array<string, mixed>
     */
    public function createDatabaseBackup(): array
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupName = "database_backup_{$timestamp}";

        try {
            Log::info('Starting database backup', ['backup_name' => $backupName]);

            $backupDir = $this->backupPath.'/'.$backupName;
            if (! is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $result = $this->backupDatabase($backupDir);
            $result['backup_name'] = $backupName;
            $result['completed_at'] = now();
            $result['status'] = 'completed';

            Log::info('Database backup completed', [
                'backup_name' => $backupName,
                'size' => $result['size'],
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Database backup failed', [
                'backup_name' => $backupName,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Create files backup.
     */
    /**
     * @return array<string, mixed>
     */
    public function createFilesBackup(): array
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupName = "files_backup_{$timestamp}";

        try {
            Log::info('Starting files backup', ['backup_name' => $backupName]);

            $backupDir = $this->backupPath.'/'.$backupName;
            if (! is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $result = $this->backupFiles($backupDir);
            $result['backup_name'] = $backupName;
            $result['completed_at'] = now();
            $result['status'] = 'completed';

            Log::info('Files backup completed', [
                'backup_name' => $backupName,
                'size' => $result['size'],
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Files backup failed', [
                'backup_name' => $backupName,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Restore from backup.
     */
    /**
     * @return array<string, mixed>
     */
    public function restoreFromBackup(string $backupName): array
    {
        try {
            Log::info('Starting restore from backup', ['backup_name' => $backupName]);

            $backupPath = $this->backupPath.'/'.$backupName;

            if (! is_dir($backupPath)) {
                throw new Exception("Backup not found: {$backupName}");
            }

            $results = [
                'backup_name' => $backupName,
                'started_at' => now(),
                'components' => [],
            ];

            // Read backup manifest
            $manifest = $this->readBackupManifest($backupPath);
            $results['manifest'] = $manifest;

            // Restore database
            $components = $manifest['components'] ?? [];
            if (is_array($components) && isset($components['database']) && is_array($components['database'])) {
                $dbInfo = $components['database'];
                $dbInfoTyped = $dbInfo;
                $results['components']['database'] = $this->restoreDatabase($backupPath, $dbInfoTyped);
            }

            // Restore files
            if (is_array($components) && isset($components['files']) && is_array($components['files'])) {
                $filesInfo = $components['files'];
                $filesInfoTyped = $filesInfo;
                $results['components']['files'] = $this->restoreFiles($backupPath, $filesInfoTyped);
            }

            // Restore configuration
            if (is_array($components) && isset($components['config']) && is_array($components['config'])) {
                $configInfo = $components['config'];
                $configInfoTyped = $configInfo;
                $results['components']['config'] = $this->restoreConfiguration($backupPath, $configInfoTyped);
            }

            $results['completed_at'] = now();
            $results['status'] = 'completed';

            Log::info('Restore completed', [
                'backup_name' => $backupName,
                'duration' => $results['completed_at']->diffInSeconds($results['started_at']),
            ]);

            return $results;
        } catch (Exception $e) {
            Log::error('Restore failed', [
                'backup_name' => $backupName,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * List available backups.
     */
    /**
     * @return list<array<string, mixed>>
     */
    public function listBackups(): array
    {
        $backups = [];

        if (! is_dir($this->backupPath)) {
            return $backups;
        }

        $directories = scandir($this->backupPath);

        foreach ($directories as $directory) {
            if ($directory === '.') {
                continue;
            }
            if ($directory === '..') {
                continue;
            }
            $backupPath = $this->backupPath.'/'.$directory;

            if (is_dir($backupPath)) {
                $manifest = $this->readBackupManifest($backupPath);
                $components = $manifest['components'] ?? [];
                $componentsArray = is_array($components) ? $components : [];

                $backups[] = [
                    'name' => $directory,
                    'type' => $manifest['type'] ?? 'unknown',
                    'created_at' => isset($manifest['created_at']) && is_string($manifest['created_at']) ? $manifest['created_at'] : null,
                    'size' => $this->getBackupSize($backupPath),
                    'components' => array_keys($componentsArray),
                ];
            }
        }

        // Sort by creation date (newest first)
        usort($backups, function (array $a, array $b): int {
            $timeA = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
            $timeB = isset($b['created_at']) ? strtotime($b['created_at']) : 0;

            return $timeB - $timeA;
        });

        return $backups;
    }

    /**
     * Delete backup.
     */
    public function deleteBackup(string $backupName): bool
    {
        try {
            $backupPath = $this->backupPath.'/'.$backupName;

            if (! is_dir($backupPath)) {
                throw new Exception("Backup not found: {$backupName}");
            }

            $this->deleteDirectory($backupPath);

            Log::info('Backup deleted', ['backup_name' => $backupName]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to delete backup', [
                'backup_name' => $backupName,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Clean old backups.
     */
    public function cleanOldBackups(int $daysOld = 30): int
    {
        $deletedCount = 0;
        $cutoffDate = now()->subDays($daysOld);

        $backups = $this->listBackups();

        foreach ($backups as $backup) {
            $createdAtStr = $backup['created_at'] ?? '';
            $createdAt = is_string($createdAtStr) ? Carbon::parse($createdAtStr) : now();

            if ($createdAt->lt($cutoffDate)) {
                $backupName = $backup['name'] ?? '';
                if (is_string($backupName) && $this->deleteBackup($backupName)) {
                    $deletedCount++;
                }
            }
        }

        Log::info('Old backups cleaned', [
            'deleted_count' => $deletedCount,
            'days_old' => $daysOld,
        ]);

        return $deletedCount;
    }

    /**
     * Backup database.
     */
    /**
     * @return array<string, mixed>
     */
    private function backupDatabase(string $backupDir): array
    {
        $dbConfig = config('database.connections.mysql');
        $filename = 'database.sql';
        $filepath = $backupDir.'/'.$filename;

        $dbConfigArray = is_array($dbConfig) ? $dbConfig : [];
        $host = is_string($dbConfigArray['host'] ?? null) ? $dbConfigArray['host'] : 'localhost';
        $port = is_string($dbConfigArray['port'] ?? null) ? $dbConfigArray['port'] : '3306';
        $username = is_string($dbConfigArray['username'] ?? null) ? $dbConfigArray['username'] : 'root';
        $password = is_string($dbConfigArray['password'] ?? null) ? $dbConfigArray['password'] : '';
        $database = is_string($dbConfigArray['database'] ?? null) ? $dbConfigArray['database'] : 'database';

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
            $host,
            $port,
            $username,
            $password,
            $database,
            $filepath
        );

        $result = Process::run($command);

        if (! $result->successful()) {
            throw new Exception('Database backup failed: '.$result->errorOutput());
        }

        return [
            'filename' => $filename,
            'size' => filesize($filepath),
            'status' => 'completed',
        ];
    }

    /**
     * Backup files.
     */
    /**
     * @return array<string, mixed>
     */
    private function backupFiles(string $backupDir): array
    {
        $filesDir = $backupDir.'/files';
        mkdir($filesDir, 0755, true);

        $sourceDirs = [
            'storage/app' => storage_path('app'),
            'storage/logs' => storage_path('logs'),
            'public/uploads' => public_path('uploads'),
        ];

        $backedUpDirs = [];

        foreach ($sourceDirs as $name => $sourcePath) {
            if (is_dir($sourcePath)) {
                $destPath = $filesDir.'/'.$name;
                $this->copyDirectory($sourcePath, $destPath);
                $backedUpDirs[] = $name;
            }
        }

        return [
            'directories' => $backedUpDirs,
            'size' => $this->getDirectorySize($filesDir),
            'status' => 'completed',
        ];
    }

    /**
     * Backup configuration.
     */
    /**
     * @return array<string, mixed>
     */
    private function backupConfiguration(string $backupDir): array
    {
        $configDir = $backupDir.'/config';
        mkdir($configDir, 0755, true);

        $configFiles = [
            '.env' => base_path('.env'),
            'app.php' => config_path('app.php'),
            'database.php' => config_path('database.php'),
            'cache.php' => config_path('cache.php'),
        ];

        $backedUpFiles = [];

        foreach ($configFiles as $name => $sourcePath) {
            if (file_exists($sourcePath)) {
                $destPath = $configDir.'/'.$name;
                copy($sourcePath, $destPath);
                $backedUpFiles[] = $name;
            }
        }

        return [
            'files' => $backedUpFiles,
            'size' => $this->getDirectorySize($configDir),
            'status' => 'completed',
        ];
    }

    /**
     * Create backup manifest.
     */
    /**
     * @param  array<string, mixed>  $results
     * @return array<string, mixed>
     */
    private function createBackupManifest(string $backupDir, array $results): array
    {
        $manifest = [
            'type' => 'full_backup',
            'created_at' => now()->toISOString(),
            'version' => '1.0',
            'components' => $results['components'],
        ];

        $manifestPath = $backupDir.'/manifest.json';
        file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT));

        return [
            'filename' => 'manifest.json',
            'size' => filesize($manifestPath),
            'status' => 'completed',
        ];
    }

    /**
     * Compress backup.
     */
    /**
     * @return array<string, mixed>
     */
    private function compressBackup(string $backupDir, string $backupName): array
    {
        $archivePath = $this->backupPath.'/'.$backupName.'.tar.gz';

        $command = "tar -czf {$archivePath} -C ".dirname($backupDir).' '.basename($backupDir);

        $result = Process::run($command);

        if (! $result->successful()) {
            throw new Exception('Backup compression failed: '.$result->errorOutput());
        }

        // Remove uncompressed directory
        $this->deleteDirectory($backupDir);

        return [
            'archive_path' => $archivePath,
            'size' => filesize($archivePath),
            'status' => 'completed',
        ];
    }

    /**
     * Read backup manifest.
     */
    /**
     * @return array<string, mixed>
     */
    private function readBackupManifest(string $backupPath): array
    {
        $manifestPath = $backupPath.'/manifest.json';

        if (! file_exists($manifestPath)) {
            return [];
        }

        $content = file_get_contents($manifestPath);
        if ($content === false) {
            return [];
        }

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Restore database.
     */
    /**
     * @param  array<mixed, mixed>  $dbInfo
     * @return array<string, mixed>
     */
    private function restoreDatabase(string $backupPath, array $dbInfo): array
    {
        $dbConfig = config('database.connections.mysql');
        $filename = $dbInfo['filename'] ?? 'database.sql';
        $sqlFile = $backupPath.'/'.(is_string($filename) ? $filename : 'database.sql');

        if (! file_exists($sqlFile)) {
            throw new Exception('Database backup file not found');
        }

        $dbConfigArray = is_array($dbConfig) ? $dbConfig : [];
        $host = is_string($dbConfigArray['host'] ?? null) ? $dbConfigArray['host'] : 'localhost';
        $port = is_string($dbConfigArray['port'] ?? null) ? $dbConfigArray['port'] : '3306';
        $username = is_string($dbConfigArray['username'] ?? null) ? $dbConfigArray['username'] : 'root';
        $password = is_string($dbConfigArray['password'] ?? null) ? $dbConfigArray['password'] : '';
        $database = is_string($dbConfigArray['database'] ?? null) ? $dbConfigArray['database'] : 'database';

        $command = sprintf(
            'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
            $host,
            $port,
            $username,
            $password,
            $database,
            $sqlFile
        );

        $result = Process::run($command);

        if (! $result->successful()) {
            throw new Exception('Database restore failed: '.$result->errorOutput());
        }

        return [
            'status' => 'completed',
        ];
    }

    /**
     * Restore files.
     */
    /**
     * @param  array<mixed, mixed>  $filesInfo
     * @return array<string, mixed>
     */
    private function restoreFiles(string $backupPath, array $filesInfo): array
    {
        $filesDir = $backupPath.'/files';

        if (! is_dir($filesDir)) {
            throw new Exception('Files backup directory not found');
        }

        $restoredDirs = [];

        $directories = $filesInfo['directories'] ?? [];
        if (is_array($directories)) {
            foreach ($directories as $dir) {
                if (is_string($dir)) {
                    $sourcePath = $filesDir.'/'.$dir;
                    $destPath = $this->getDestinationPath($dir);

                    if (is_dir($sourcePath)) {
                        $this->copyDirectory($sourcePath, $destPath);
                        $restoredDirs[] = $dir;
                    }
                }
            }
        }

        return [
            'directories' => $restoredDirs,
            'status' => 'completed',
        ];
    }

    /**
     * Restore configuration.
     */
    /**
     * @param  array<mixed, mixed>  $configInfo
     * @return array<string, mixed>
     */
    private function restoreConfiguration(string $backupPath, array $configInfo): array
    {
        $configDir = $backupPath.'/config';

        if (! is_dir($configDir)) {
            throw new Exception('Configuration backup directory not found');
        }

        $restoredFiles = [];

        $files = $configInfo['files'] ?? [];
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_string($file)) {
                    $sourcePath = $configDir.'/'.$file;
                    $destPath = $this->getConfigDestinationPath($file);

                    if (file_exists($sourcePath)) {
                        copy($sourcePath, $destPath);
                        $restoredFiles[] = $file;
                    }
                }
            }
        }

        return [
            'files' => $restoredFiles,
            'status' => 'completed',
        ];
    }

    /**
     * Get destination path for file restoration.
     */
    private function getDestinationPath(string $dir): string
    {
        $destinations = [
            'storage/app' => storage_path('app'),
            'storage/logs' => storage_path('logs'),
            'public/uploads' => public_path('uploads'),
        ];

        return $destinations[$dir] ?? storage_path('app');
    }

    /**
     * Get configuration destination path.
     */
    private function getConfigDestinationPath(string $file): string
    {
        $destinations = [
            '.env' => base_path('.env'),
            'app.php' => config_path('app.php'),
            'database.php' => config_path('database.php'),
            'cache.php' => config_path('cache.php'),
        ];

        return $destinations[$file] ?? config_path($file);
    }

    /**
     * Copy directory recursively.
     */
    private function copyDirectory(string $source, string $dest): void
    {
        if (! is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item instanceof \SplFileInfo) {
                $destPath = $dest.DIRECTORY_SEPARATOR.$iterator->getSubPathName();

                if ($item->isDir()) {
                    mkdir($destPath, 0755, true);
                } else {
                    copy($item->getPathname(), $destPath);
                }
            }
        }
    }

    /**
     * Delete directory recursively.
     */
    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item instanceof \SplFileInfo) {
                if ($item->isDir()) {
                    rmdir($item->getPathname());
                } else {
                    unlink($item->getPathname());
                }
            }
        }

        rmdir($dir);
    }

    /**
     * Get directory size.
     */
    private function getDirectorySize(string $dir): int
    {
        $size = 0;

        if (! is_dir($dir)) {
            return $size;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Get backup size.
     */
    private function getBackupSize(string $backupPath): int
    {
        return $this->getDirectorySize($backupPath);
    }
}
