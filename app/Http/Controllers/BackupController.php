<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class BackupController extends Controller
{
    protected string $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
    }

    /**
     * Get all backups.
     */
    public function index(): JsonResponse
    {
        try {
            $backups = $this->getBackupsList();

            return response()->json([
                'success' => true,
                'data' => $backups,
                'message' => 'Backups retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting backups: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get backups',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new backup.
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'type' => 'required|in:full,database,files',
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string|max:500',
            ]);

            $type = $request->input('type');
            $name = $request->input('name', 'backup_' . now()->format('Y-m-d_H-i-s'));
            $description = $request->input('description', '');

            $backup = $this->createBackup([
                'type' => $type,
                'name' => $name,
                'description' => $description,
            ]);

            return response()->json([
                'success' => true,
                'data' => $backup,
                'message' => 'Backup created successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating backup: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a backup.
     */
    public function download(string $id): JsonResponse
    {
        try {
            $backup = $this->getBackupById($id);

            if (! $backup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup not found',
                ], 404);
            }

            $filePath = $this->backupPath . '/' . $backup['filename'];

            if (! file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Backup ready for download',
                'data' => [
                    'filename' => $backup['filename'],
                    'download_url' => url('storage/backups/' . $backup['filename']),
                    'size' => filesize($filePath),
                    'expires_at' => now()->addHours(24)->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error downloading backup: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to download backup',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a backup.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $backup = $this->getBackupById($id);

            if (! $backup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup not found',
                ], 404);
            }

            $filePath = $this->backupPath . '/' . $backup['filename'];

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            Log::info('Backup deleted: ' . $id);

            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting backup: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete backup',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore from backup.
     */
    public function restore(string $id): JsonResponse
    {
        try {
            $backup = $this->getBackupById($id);

            if (! $backup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup not found',
                ], 404);
            }

            $result = $this->restoreFromBackup($backup);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? [],
            ]);
        } catch (\Exception $e) {
            Log::error('Error restoring backup: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore backup',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get backup statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $backups = $this->getBackupsList();
            $totalSize = 0;
            $totalCount = count($backups);

            foreach ($backups as $backup) {
                $filePath = $this->backupPath . '/' . $backup['filename'];
                if (file_exists($filePath)) {
                    $totalSize += filesize($filePath);
                }
            }

            $stats = [
                'total_backups' => $totalCount,
                'total_size' => $totalSize,
                'total_size_formatted' => $this->formatBytes($totalSize),
                'oldest_backup' => null,
                'newest_backup' => null,
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Backup statistics retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting backup statistics: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get backup statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get backups list.
     *
     * @return list<array<string, int|string>>
     */
    private function getBackupsList(): array
    {
        try {
            if (! is_dir($this->backupPath)) {
                mkdir($this->backupPath, 0755, true);
            }

            $files = scandir($this->backupPath);
            if ($files === false) {
                return [];
            }

            $backups = [];
            foreach ($files as $file) {
                if ($file === '.') {
                    continue;
                }
                if ($file === '..') {
                    continue;
                }
                $filePath = $this->backupPath . '/' . $file;
                if (is_file($filePath)) {
                    $fileSize = filesize($filePath);
                    $fileTime = filemtime($filePath);
                    $backups[] = [
                        'id' => pathinfo($file, PATHINFO_FILENAME),
                        'filename' => $file,
                        'size' => $fileSize !== false ? $fileSize : 0,
                        'size_formatted' => $this->formatBytes($fileSize !== false ? $fileSize : 0),
                        'created_at' => $fileTime !== false ? date('Y-m-d H:i:s', $fileTime) : now()->format('Y-m-d H:i:s'),
                        'type' => $this->getBackupType($file),
                    ];
                }
            }

            // Sort by creation time (newest first)
            usort($backups, fn(array $a, array $b): int => strtotime($b['created_at']) - strtotime($a['created_at']));

            return $backups;
        } catch (\Exception $e) {
            Log::error('Error getting backups list: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Create backup.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function createBackup(array $options): array
    {
        try {
            $type = $options['type'] ?? 'full';
            $name = $options['name'] ?? 'backup_' . now()->format('Y-m-d_H-i-s');
            $description = $options['description'] ?? '';

            $filename = $name . '.zip';
            $filePath = $this->backupPath . '/' . $filename;

            if (! is_dir($this->backupPath)) {
                mkdir($this->backupPath, 0755, true);
            }

            $zip = new ZipArchive;
            if ($zip->open($filePath, ZipArchive::CREATE) !== true) {
                throw new \RuntimeException('Cannot create backup file');
            }

            if ($type === 'full' || $type === 'database') {
                $this->backupDatabase($zip);
            }

            if ($type === 'full' || $type === 'files') {
                $this->backupFiles($zip, $options);
            }

            $zip->close();

            Log::info('Backup created: ' . $filename);

            $fileSize = filesize($filePath);

            return [
                'id' => pathinfo($filename, PATHINFO_FILENAME),
                'filename' => $filename,
                'type' => $type,
                'size' => $fileSize !== false ? $fileSize : 0,
                'size_formatted' => $this->formatBytes($fileSize !== false ? $fileSize : 0),
                'created_at' => now()->toISOString(),
                'description' => $description,
            ];
        } catch (\Exception $e) {
            Log::error('Error creating backup: ' . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Backup database.
     */
    private function backupDatabase(ZipArchive $zip): void
    {
        try {
            // Create database dump
            $dumpFile = storage_path('app/temp/database_dump.sql');
            $dumpDir = dirname($dumpFile);

            if (! is_dir($dumpDir)) {
                mkdir($dumpDir, 0755, true);
            }

            // Use Laravel's database backup command
            Artisan::call('db:backup', [
                '--path' => $dumpFile,
                '--force' => true,
            ]);

            if (file_exists($dumpFile)) {
                $zip->addFile($dumpFile, 'database_dump.sql');
            }
        } catch (\Exception $e) {
            Log::error('Error backing up database: ' . $e->getMessage());
        }
    }

    /**
     * Backup files.
     *
     * @param  array<string, mixed>  $options
     */
    private function backupFiles(ZipArchive $zip, array $options): void
    {
        try {
            $directories = $options['directories'] ?? [
                'app',
                'config',
                'database',
                'resources',
                'routes',
            ];

            foreach ($directories as $dir) {
                if (is_string($dir)) {
                    $this->addDirectoryToZip($zip, base_path($dir), $dir);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error backing up files: ' . $e->getMessage());
        }
    }

    /**
     * Add directory to zip.
     */
    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipPath): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile()) {
                $realPath = $file->getRealPath();
                if ($realPath !== false) {
                    $relativePath = $zipPath . '/' . substr($realPath, strlen($dir) + 1);
                    $zip->addFile($realPath, $relativePath);
                }
            }
        }
    }

    /**
     * Get backup by ID.
     *
     * @return array<string, mixed>|null
     */
    private function getBackupById(string $id): ?array
    {
        $backups = $this->getBackupsList();

        foreach ($backups as $backup) {
            if ($backup['id'] === $id) {
                return $backup;
            }
        }

        return null;
    }

    /**
     * Restore from backup.
     *
     * @param  array<string, mixed>  $backup
     * @return array<string, mixed>
     */
    private function restoreFromBackup(array $backup): array
    {
        try {
            $filePath = $this->backupPath . '/' . $backup['filename'];

            if (! file_exists($filePath)) {
                return [
                    'success' => false,
                    'message' => 'Backup file not found',
                ];
            }

            $zip = new ZipArchive;
            if ($zip->open($filePath) !== true) {
                return [
                    'success' => false,
                    'message' => 'Cannot open backup file',
                ];
            }

            // Extract to temporary directory
            $tempDir = storage_path('app/temp/restore_' . uniqid());
            mkdir($tempDir, 0755, true);

            $zip->extractTo($tempDir);
            $zip->close();

            // Restore database if present
            $dbDumpFile = $tempDir . '/database_dump.sql';
            if (file_exists($dbDumpFile)) {
                $this->restoreDatabase($dbDumpFile);
            }

            // Restore files
            $this->restoreFiles($tempDir);

            // Clean up
            $this->removeDirectory($tempDir);

            return [
                'success' => true,
                'message' => 'Backup restored successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Error restoring backup: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to restore backup: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Restore database.
     */
    private function restoreDatabase(string $dumpFile): void
    {
        try {
            // Use Laravel's database restore command
            Artisan::call('db:restore', [
                '--path' => $dumpFile,
                '--force' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Error restoring database: ' . $e->getMessage());
        }
    }

    /**
     * Restore files.
     */
    private function restoreFiles(string $tempDir): void
    {
        try {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if ($file instanceof \SplFileInfo && $file->isFile()) {
                    $realPath = $file->getRealPath();
                    if ($realPath !== false) {
                        $relativePath = substr($realPath, strlen($tempDir) + 1);
                        $targetPath = base_path($relativePath);

                        $targetDir = dirname($targetPath);
                        if (! is_dir($targetDir)) {
                            mkdir($targetDir, 0755, true);
                        }

                        copy($realPath, $targetPath);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error restoring files: ' . $e->getMessage());
        }
    }

    /**
     * Get backup type from filename.
     */
    private function getBackupType(string $filename): string
    {
        if (str_contains($filename, 'database')) {
            return 'database';
        }
        if (str_contains($filename, 'files')) {
            return 'files';
        }

        return 'full';
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= 1024 ** $pow;

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Remove directory recursively.
     */
    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
