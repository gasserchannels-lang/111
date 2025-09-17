<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CloudStorageService
{
    private readonly string $disk;

    public function __construct()
    {
        $this->disk = is_string(config('filesystems.cloud_disk', 's3')) ? config('filesystems.cloud_disk', 's3') : 's3';
    }

    /**
     * Upload file to cloud storage.
     */
    /**
     * @return array<string, mixed>
     */
    public function uploadFile(UploadedFile $file, string $path = 'images', ?string $filename = null): array
    {
        try {
            $filename ??= $this->generateUniqueFilename($file);
            $fullPath = $path.'/'.$filename;

            // Upload to cloud storage
            $uploadedPath = Storage::disk($this->disk)->putFileAs($path, $file, $filename);

            if (! $uploadedPath) {
                throw new Exception('Failed to upload file to cloud storage');
            }

            // Get file URL
            $url = Storage::disk($this->disk)->url($uploadedPath);

            // Get file metadata
            $metadata = $this->getFileMetadata($uploadedPath);

            Log::info('File uploaded to cloud storage', [
                'path' => $uploadedPath,
                'url' => $url,
                'size' => $metadata['size'],
                'disk' => $this->disk,
            ]);

            return [
                'path' => $uploadedPath,
                'url' => $url,
                'filename' => $filename,
                'size' => $metadata['size'],
                'mime_type' => $metadata['mime_type'],
                'disk' => $this->disk,
            ];
        } catch (Exception $e) {
            Log::error('Failed to upload file to cloud storage', [
                'error' => $e->getMessage(),
                'path' => $path,
                'filename' => $filename,
                'disk' => $this->disk,
            ]);

            throw $e;
        }
    }

    /**
     * Upload multiple files to cloud storage.
     */
    /**
     * @param  array<UploadedFile>  $files
     * @return list<array<string, mixed>>
     */
    public function uploadMultipleFiles(array $files, string $path = 'images'): array
    {
        $results = [];

        foreach ($files as $file) {
            try {
                $results[] = $this->uploadFile($file, $path);
            } catch (Exception $e) {
                $results[] = [
                    'error' => $e->getMessage(),
                    'filename' => $file->getClientOriginalName(),
                ];
            }
        }

        return $results;
    }

    /**
     * Delete file from cloud storage.
     */
    public function deleteFile(string $path): bool
    {
        try {
            $deleted = Storage::disk($this->disk)->delete($path);

            if ($deleted) {
                Log::info('File deleted from cloud storage', [
                    'path' => $path,
                    'disk' => $this->disk,
                ]);
            }

            return $deleted;
        } catch (Exception $e) {
            Log::error('Failed to delete file from cloud storage', [
                'error' => $e->getMessage(),
                'path' => $path,
                'disk' => $this->disk,
            ]);

            return false;
        }
    }

    /**
     * Delete multiple files from cloud storage.
     */
    /**
     * @param  list<string>  $paths
     * @return array<string, bool>
     */
    public function deleteMultipleFiles(array $paths): array
    {
        $results = [];

        foreach ($paths as $path) {
            $results[$path] = $this->deleteFile($path);
        }

        return $results;
    }

    /**
     * Copy file within cloud storage.
     */
    public function copyFile(string $sourcePath, string $destinationPath): bool
    {
        try {
            $copied = Storage::disk($this->disk)->copy($sourcePath, $destinationPath);

            if ($copied) {
                Log::info('File copied in cloud storage', [
                    'source' => $sourcePath,
                    'destination' => $destinationPath,
                    'disk' => $this->disk,
                ]);
            }

            return $copied;
        } catch (Exception $e) {
            Log::error('Failed to copy file in cloud storage', [
                'error' => $e->getMessage(),
                'source' => $sourcePath,
                'destination' => $destinationPath,
                'disk' => $this->disk,
            ]);

            return false;
        }
    }

    /**
     * Move file within cloud storage.
     */
    public function moveFile(string $sourcePath, string $destinationPath): bool
    {
        try {
            $moved = Storage::disk($this->disk)->move($sourcePath, $destinationPath);

            if ($moved) {
                Log::info('File moved in cloud storage', [
                    'source' => $sourcePath,
                    'destination' => $destinationPath,
                    'disk' => $this->disk,
                ]);
            }

            return $moved;
        } catch (Exception $e) {
            Log::error('Failed to move file in cloud storage', [
                'error' => $e->getMessage(),
                'source' => $sourcePath,
                'destination' => $destinationPath,
                'disk' => $this->disk,
            ]);

            return false;
        }
    }

    /**
     * Get file URL.
     */
    public function getFileUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Get file metadata.
     */
    /**
     * @return array<string, mixed>
     */
    public function getFileMetadata(string $path): array
    {
        try {
            $size = Storage::disk($this->disk)->size($path);
            $mimeType = Storage::disk($this->disk)->mimeType($path);
            $lastModified = Storage::disk($this->disk)->lastModified($path);

            return [
                'size' => $size,
                'mime_type' => $mimeType,
                'last_modified' => $lastModified,
                'url' => $this->getFileUrl($path),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get file metadata', [
                'error' => $e->getMessage(),
                'path' => $path,
                'disk' => $this->disk,
            ]);

            return [
                'size' => 0,
                'mime_type' => 'unknown',
                'last_modified' => 0,
                'url' => '',
            ];
        }
    }

    /**
     * Check if file exists.
     */
    public function fileExists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * List files in directory.
     */
    /**
     * @return list<array<string, mixed>>
     */
    public function listFiles(string $path = ''): array
    {
        try {
            $files = Storage::disk($this->disk)->files($path);

            return array_values(array_map(fn(string $file): array => [
                'path' => $file,
                'url' => $this->getFileUrl($file),
                'metadata' => $this->getFileMetadata($file),
            ], $files));
        } catch (Exception $e) {
            Log::error('Failed to list files', [
                'error' => $e->getMessage(),
                'path' => $path,
                'disk' => $this->disk,
            ]);

            return [];
        }
    }

    /**
     * Get directory size.
     */
    public function getDirectorySize(string $path = ''): int
    {
        try {
            $files = Storage::disk($this->disk)->allFiles($path);
            $totalSize = 0;

            foreach ($files as $file) {
                $totalSize += Storage::disk($this->disk)->size($file);
            }

            return $totalSize;
        } catch (Exception $e) {
            Log::error('Failed to get directory size', [
                'error' => $e->getMessage(),
                'path' => $path,
                'disk' => $this->disk,
            ]);

            return 0;
        }
    }

    /**
     * Clean up old files.
     */
    public function cleanupOldFiles(string $path = '', int $daysOld = 30): int
    {
        try {
            $cutoffTime = now()->subDays($daysOld)->timestamp;
            $files = Storage::disk($this->disk)->allFiles($path);
            $deletedCount = 0;

            foreach ($files as $file) {
                $fileTime = Storage::disk($this->disk)->lastModified($file);

                if ($fileTime < $cutoffTime && Storage::disk($this->disk)->delete($file)) {
                    $deletedCount++;
                }
            }

            Log::info('Old files cleaned up', [
                'path' => $path,
                'days_old' => $daysOld,
                'deleted_count' => $deletedCount,
                'disk' => $this->disk,
            ]);

            return $deletedCount;
        } catch (Exception $e) {
            Log::error('Failed to cleanup old files', [
                'error' => $e->getMessage(),
                'path' => $path,
                'days_old' => $daysOld,
                'disk' => $this->disk,
            ]);

            return 0;
        }
    }

    /**
     * Generate unique filename.
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $timestamp = time();
        $random = \Illuminate\Support\Str::random(8);

        return "{$name}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Upload image with optimization.
     */
    /**
     * @param  array<string, array<int>>  $sizes
     * @return array<string, array<string, mixed>>
     */
    public function uploadOptimizedImage(UploadedFile $file, string $path = 'images', array $sizes = []): array
    {
        try {
            $defaultSizes = [
                'thumbnail' => [150, 150],
                'small' => [300, 300],
                'medium' => [600, 600],
                'large' => [1200, 1200],
            ];

            $sizes = array_merge($defaultSizes, $sizes);
            $results = [];

            // Upload original
            $original = $this->uploadFile($file, $path);
            $results['original'] = $original;

            // Create optimized versions
            foreach (array_keys($sizes) as $sizeName) {
                $optimizedFile = $this->createOptimizedImage($file);
                $sizeNameStr = $sizeName;
                $originalFilename = is_string($original['filename'] ?? null) ? $original['filename'] : '';
                $optimizedPath = $path.'/optimized/'.$sizeNameStr.'_'.$originalFilename;

                $optimized = $this->uploadFile($optimizedFile, $path.'/optimized', $sizeNameStr.'_'.$originalFilename);
                $results[$sizeName] = $optimized;
            }

            return $results;
        } catch (Exception $e) {
            Log::error('Failed to upload optimized image', [
                'error' => $e->getMessage(),
                'path' => $path,
                'disk' => $this->disk,
            ]);

            throw $e;
        }
    }

    /**
     * Create optimized image.
     */
    private function createOptimizedImage(UploadedFile $file): UploadedFile
    {
        // This would use an image processing library like Intervention Image
        // For now, return the original file
        return $file;
    }

    /**
     * Get storage statistics.
     */
    /**
     * @return array<string, mixed>
     */
    public function getStorageStats(): array
    {
        try {
            $files = Storage::disk($this->disk)->allFiles();
            $totalSize = 0;
            $fileCount = count($files);
            $mimeTypes = [];

            foreach ($files as $file) {
                $size = Storage::disk($this->disk)->size($file);
                $totalSize += $size;

                $mimeType = Storage::disk($this->disk)->mimeType($file);
                $mimeTypes[$mimeType] = ($mimeTypes[$mimeType] ?? 0) + 1;
            }

            return [
                'total_files' => $fileCount,
                'total_size' => $totalSize,
                'total_size_mb' => round($totalSize / 1024 / 1024, 2),
                'mime_types' => $mimeTypes,
                'disk' => $this->disk,
            ];
        } catch (Exception $e) {
            Log::error('Failed to get storage stats', [
                'error' => $e->getMessage(),
                'disk' => $this->disk,
            ]);

            return [
                'total_files' => 0,
                'total_size' => 0,
                'total_size_mb' => 0,
                'mime_types' => [],
                'disk' => $this->disk,
            ];
        }
    }
}
