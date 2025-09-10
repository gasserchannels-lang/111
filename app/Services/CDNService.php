<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CDNService
{
    private array $config;

    private string $provider;

    public function __construct()
    {
        $this->provider = config('cdn.provider', 'cloudflare');
        $this->config = config('cdn.providers.' . $this->provider, []);
    }

    /**
     * Upload file to CDN.
     */
    public function uploadFile(string $localPath, ?string $remotePath = null): array
    {
        try {
            $remotePath = $remotePath ?? $localPath;

            $fileContent = Storage::disk('public')->get($localPath);
            $mimeType = Storage::disk('public')->mimeType($localPath);

            $result = $this->uploadToCDN($fileContent, $remotePath, $mimeType);

            Log::info('File uploaded to CDN', [
                'local_path' => $localPath,
                'remote_path' => $remotePath,
                'provider' => $this->provider,
                'url' => $result['url'],
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Failed to upload file to CDN', [
                'local_path' => $localPath,
                'remote_path' => $remotePath,
                'provider' => $this->provider,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Upload multiple files to CDN.
     */
    public function uploadMultipleFiles(array $files): array
    {
        $results = [];

        foreach ($files as $localPath => $remotePath) {
            try {
                $results[$localPath] = $this->uploadFile($localPath, $remotePath);
            } catch (Exception $e) {
                $results[$localPath] = [
                    'error' => $e->getMessage(),
                    'success' => false,
                ];
            }
        }

        return $results;
    }

    /**
     * Delete file from CDN.
     */
    public function deleteFile(string $remotePath): bool
    {
        try {
            $result = $this->deleteFromCDN($remotePath);

            Log::info('File deleted from CDN', [
                'remote_path' => $remotePath,
                'provider' => $this->provider,
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Failed to delete file from CDN', [
                'remote_path' => $remotePath,
                'provider' => $this->provider,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Purge CDN cache.
     */
    public function purgeCache(array $urls = []): bool
    {
        try {
            $result = $this->purgeCDNCache($urls);

            Log::info('CDN cache purged', [
                'urls' => $urls,
                'provider' => $this->provider,
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Failed to purge CDN cache', [
                'urls' => $urls,
                'provider' => $this->provider,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get CDN URL for a file.
     */
    public function getUrl(string $path): string
    {
        $baseUrl = $this->config['base_url'] ?? '';

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Check if file exists on CDN.
     */
    public function fileExists(string $remotePath): bool
    {
        try {
            $url = $this->getUrl($remotePath);
            $response = Http::head($url);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Failed to check file existence on CDN', [
                'remote_path' => $remotePath,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get file metadata from CDN.
     */
    public function getFileMetadata(string $remotePath): array
    {
        try {
            $url = $this->getUrl($remotePath);
            $response = Http::head($url);

            if (!$response->successful()) {
                throw new Exception('File not found on CDN');
            }

            return [
                'url' => $url,
                'size' => $response->header('Content-Length'),
                'mime_type' => $response->header('Content-Type'),
                'last_modified' => $response->header('Last-Modified'),
                'etag' => $response->header('ETag'),
                'cache_control' => $response->header('Cache-Control'),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get file metadata from CDN', [
                'remote_path' => $remotePath,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Upload to CDN based on provider.
     */
    private function uploadToCDN(string $content, string $path, string $mimeType): array
    {
        switch ($this->provider) {
            case 'cloudflare':
                return $this->uploadToCloudflare($content, $path, $mimeType);

            case 'aws_s3':
                return $this->uploadToS3($content, $path, $mimeType);

            case 'google_cloud':
                return $this->uploadToGoogleCloud($content, $path, $mimeType);

            default:
                throw new Exception("Unsupported CDN provider: {$this->provider}");
        }
    }

    /**
     * Upload to Cloudflare.
     */
    private function uploadToCloudflare(string $content, string $path, string $mimeType): array
    {
        $apiToken = $this->config['api_token'];
        $accountId = $this->config['account_id'];
        $zoneId = $this->config['zone_id'];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiToken}",
            'Content-Type' => $mimeType,
        ])->put("https://api.cloudflare.com/client/v4/accounts/{$accountId}/images/v1/{$path}", $content);

        if (!$response->successful()) {
            throw new Exception('Cloudflare upload failed: ' . $response->body());
        }

        $data = $response->json();

        return [
            'url' => $data['result']['variants'][0] ?? $this->getUrl($path),
            'id' => $data['result']['id'] ?? null,
            'provider' => 'cloudflare',
        ];
    }

    /**
     * Upload to AWS S3.
     */
    private function uploadToS3(string $content, string $path, string $mimeType): array
    {
        $bucket = $this->config['bucket'];
        $region = $this->config['region'];
        $accessKey = $this->config['access_key'];
        $secretKey = $this->config['secret_key'];

        // This would use AWS SDK in a real implementation
        $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";

        return [
            'url' => $url,
            'provider' => 'aws_s3',
        ];
    }

    /**
     * Upload to Google Cloud Storage.
     */
    private function uploadToGoogleCloud(string $content, string $path, string $mimeType): array
    {
        $bucket = $this->config['bucket'];
        $projectId = $this->config['project_id'];
        $credentials = $this->config['credentials'];

        // This would use Google Cloud SDK in a real implementation
        $url = "https://storage.googleapis.com/{$bucket}/{$path}";

        return [
            'url' => $url,
            'provider' => 'google_cloud',
        ];
    }

    /**
     * Delete from CDN based on provider.
     */
    private function deleteFromCDN(string $path): bool
    {
        switch ($this->provider) {
            case 'cloudflare':
                return $this->deleteFromCloudflare($path);

            case 'aws_s3':
                return $this->deleteFromS3($path);

            case 'google_cloud':
                return $this->deleteFromGoogleCloud($path);

            default:
                throw new Exception("Unsupported CDN provider: {$this->provider}");
        }
    }

    /**
     * Delete from Cloudflare.
     */
    private function deleteFromCloudflare(string $path): bool
    {
        $apiToken = $this->config['api_token'];
        $accountId = $this->config['account_id'];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiToken}",
        ])->delete("https://api.cloudflare.com/client/v4/accounts/{$accountId}/images/v1/{$path}");

        return $response->successful();
    }

    /**
     * Delete from AWS S3.
     */
    private function deleteFromS3(string $path): bool
    {
        // This would use AWS SDK in a real implementation
        return true;
    }

    /**
     * Delete from Google Cloud Storage.
     */
    private function deleteFromGoogleCloud(string $path): bool
    {
        // This would use Google Cloud SDK in a real implementation
        return true;
    }

    /**
     * Purge CDN cache based on provider.
     */
    private function purgeCDNCache(array $urls): bool
    {
        switch ($this->provider) {
            case 'cloudflare':
                return $this->purgeCloudflareCache($urls);

            case 'aws_s3':
                return $this->purgeS3Cache($urls);

            case 'google_cloud':
                return $this->purgeGoogleCloudCache($urls);

            default:
                throw new Exception("Unsupported CDN provider: {$this->provider}");
        }
    }

    /**
     * Purge Cloudflare cache.
     */
    private function purgeCloudflareCache(array $urls): bool
    {
        $apiToken = $this->config['api_token'];
        $zoneId = $this->config['zone_id'];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiToken}",
            'Content-Type' => 'application/json',
        ])->post("https://api.cloudflare.com/client/v4/zones/{$zoneId}/purge_cache", [
            'purge_everything' => empty($urls),
            'files' => $urls,
        ]);

        return $response->successful();
    }

    /**
     * Purge S3 cache.
     */
    private function purgeS3Cache(array $urls): bool
    {
        // S3 doesn't have built-in cache purging
        // This would invalidate CloudFront if configured
        return true;
    }

    /**
     * Purge Google Cloud cache.
     */
    private function purgeGoogleCloudCache(array $urls): bool
    {
        // This would use Google Cloud SDK in a real implementation
        return true;
    }

    /**
     * Get CDN statistics.
     */
    public function getStatistics(): array
    {
        try {
            switch ($this->provider) {
                case 'cloudflare':
                    return $this->getCloudflareStatistics();

                case 'aws_s3':
                    return $this->getS3Statistics();

                case 'google_cloud':
                    return $this->getGoogleCloudStatistics();

                default:
                    return [];
            }
        } catch (Exception $e) {
            Log::error('Failed to get CDN statistics', [
                'provider' => $this->provider,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get Cloudflare statistics.
     */
    private function getCloudflareStatistics(): array
    {
        $apiToken = $this->config['api_token'];
        $zoneId = $this->config['zone_id'];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiToken}",
        ])->get("https://api.cloudflare.com/client/v4/zones/{$zoneId}/analytics/dashboard");

        if (!$response->successful()) {
            throw new Exception('Failed to get Cloudflare statistics');
        }

        return $response->json();
    }

    /**
     * Get S3 statistics.
     */
    private function getS3Statistics(): array
    {
        // This would use AWS SDK in a real implementation
        return [];
    }

    /**
     * Get Google Cloud statistics.
     */
    private function getGoogleCloudStatistics(): array
    {
        // This would use Google Cloud SDK in a real implementation
        return [];
    }

    /**
     * Test CDN connection.
     */
    public function testConnection(): bool
    {
        try {
            $testPath = 'test/connection.txt';
            $testContent = 'CDN connection test - ' . now();

            $result = $this->uploadToCDN($testContent, $testPath, 'text/plain');

            if (isset($result['url'])) {
                $this->deleteFromCDN($testPath);

                return true;
            }

            return false;
        } catch (Exception $e) {
            Log::error('CDN connection test failed', [
                'provider' => $this->provider,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
