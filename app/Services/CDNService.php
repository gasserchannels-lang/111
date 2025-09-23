<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CDNService
{
    /**
     * @var array<string, mixed>
     */
    private array $config;

    private readonly string $provider;

    public function __construct()
    {
        $this->provider = is_string(config('cdn.provider', 'cloudflare')) ? config('cdn.provider', 'cloudflare') : 'cloudflare';
        $this->config = is_array(config('cdn.providers.'.$this->provider, [])) ? config('cdn.providers.'.$this->provider, []) : [];
    }

    /**
     * Upload file to CDN.
     */
    /**
     * @return array<string, mixed>
     */
    public function uploadFile(string $localPath, ?string $remotePath = null): array
    {
        try {
            $remotePath ??= $localPath;

            $fileContent = Storage::disk('public')->get($localPath);
            $mimeType = Storage::disk('public')->mimeType($localPath);

            $result = $this->uploadToCDN($fileContent ?? '', $remotePath, $mimeType ?: 'application/octet-stream');

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
    /**
     * @param  array<string, string>  $files
     * @return array<string, array<string, mixed>>
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
    /**
     * @param  list<string>  $urls
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

        return rtrim(is_string($baseUrl) ? $baseUrl : '', '/').'/'.ltrim($path, '/');
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
    /**
     * @return array<string, mixed>
     */
    public function getFileMetadata(string $remotePath): array
    {
        try {
            $url = $this->getUrl($remotePath);
            $response = Http::head($url);

            if (! $response->successful()) {
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
    /**
     * @return array<string, mixed>
     */
    private function uploadToCDN(string $content, string $path, string $mimeType): array
    {
        return match ($this->provider) {
            'cloudflare' => $this->uploadToCloudflare($content, $path, $mimeType),
            'aws_s3' => $this->uploadToS3($path),
            'google_cloud' => $this->uploadToGoogleCloud($path),
            default => throw new Exception("Unsupported CDN provider: {$this->provider}"),
        };
    }

    /**
     * Upload to Cloudflare.
     */
    /**
     * @return array<string, mixed>
     */
    private function uploadToCloudflare(string $content, string $path, string $mimeType): array
    {
        $apiToken = is_string($this->config['api_token'] ?? null) ? $this->config['api_token'] : '';
        $accountId = is_string($this->config['account_id'] ?? null) ? $this->config['account_id'] : '';
        if (is_string($this->config['zone_id'] ?? null)) {
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiToken,
            'Content-Type' => $mimeType,
        ])->put('https://api.cloudflare.com/client/v4/accounts/'.$accountId."/images/v1/{$path}", ['content' => $content]);

        if (! $response->successful()) {
            throw new Exception('Cloudflare upload failed: '.$response->body());
        }

        $data = $response->json() ?? [];
        if (! is_array($data)) {
            $data = [];
        }

        $result = isset($data['result']) && is_array($data['result']) ? $data['result'] : [];
        $variants = isset($result['variants']) && is_array($result['variants']) ? $result['variants'] : [];
        $id = $result['id'] ?? null;

        return [
            'url' => $variants !== [] && isset($variants[0]) && is_string($variants[0]) ? $variants[0] : $this->getUrl($path),
            'id' => $id,
            'provider' => 'cloudflare',
        ];
    }

    /**
     * Upload to AWS S3.
     */
    /**
     * @return array<string, mixed>
     */
    private function uploadToS3(string $path): array
    {
        $bucket = is_string($this->config['bucket'] ?? null) ? $this->config['bucket'] : '';
        $region = is_string($this->config['region'] ?? null) ? $this->config['region'] : '';
        if (is_string($this->config['access_key'] ?? null)) {
        }
        if (is_string($this->config['secret_key'] ?? null)) {
        }

        // This would use AWS SDK in a real implementation
        $url = 'https://'.$bucket.'.s3.'.$region.".amazonaws.com/{$path}";

        return [
            'url' => $url,
            'provider' => 'aws_s3',
        ];
    }

    /**
     * Upload to Google Cloud Storage.
     */
    /**
     * @return array<string, mixed>
     */
    private function uploadToGoogleCloud(string $path): array
    {
        $bucket = is_string($this->config['bucket'] ?? null) ? $this->config['bucket'] : '';
        if (is_string($this->config['project_id'] ?? null)) {
        }
        if (is_array($this->config['credentials'] ?? [])) {
        }

        // This would use Google Cloud SDK in a real implementation
        $url = 'https://storage.googleapis.com/'.$bucket."/{$path}";

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
        return match ($this->provider) {
            'cloudflare' => $this->deleteFromCloudflare($path),
            'aws_s3' => $this->deleteFromS3(),
            'google_cloud' => $this->deleteFromGoogleCloud(),
            default => throw new Exception("Unsupported CDN provider: {$this->provider}"),
        };
    }

    /**
     * Delete from Cloudflare.
     */
    private function deleteFromCloudflare(string $path): bool
    {
        $apiToken = is_string($this->config['api_token'] ?? null) ? $this->config['api_token'] : '';
        $accountId = is_string($this->config['account_id'] ?? null) ? $this->config['account_id'] : '';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiToken,
        ])->delete('https://api.cloudflare.com/client/v4/accounts/'.$accountId."/images/v1/{$path}");

        return $response->successful();
    }

    /**
     * Delete from AWS S3.
     */
    private function deleteFromS3(): bool
    {
        // This would use AWS SDK in a real implementation
        return true;
    }

    /**
     * Delete from Google Cloud Storage.
     */
    private function deleteFromGoogleCloud(): bool
    {
        // This would use Google Cloud SDK in a real implementation
        return true;
    }

    /**
     * Purge CDN cache based on provider.
     */
    /**
     * @param  list<string>  $urls
     */
    private function purgeCDNCache(array $urls): bool
    {
        return match ($this->provider) {
            'cloudflare' => $this->purgeCloudflareCache($urls),
            'aws_s3' => $this->purgeS3Cache(),
            'google_cloud' => $this->purgeGoogleCloudCache(),
            default => throw new Exception("Unsupported CDN provider: {$this->provider}"),
        };
    }

    /**
     * Purge Cloudflare cache.
     */
    /**
     * @param  list<string>  $urls
     */
    private function purgeCloudflareCache(array $urls): bool
    {
        $apiToken = is_string($this->config['api_token'] ?? null) ? $this->config['api_token'] : '';
        $zoneId = is_string($this->config['zone_id'] ?? null) ? $this->config['zone_id'] : '';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiToken,
            'Content-Type' => 'application/json',
        ])->post('https://api.cloudflare.com/client/v4/zones/'.$zoneId.'/purge_cache', [
            'purge_everything' => $urls === [],
            'files' => $urls,
        ]);

        return $response->successful();
    }

    private function purgeS3Cache(): bool
    {
        // S3 doesn't have built-in cache purging
        // This would invalidate CloudFront if configured
        return true;
    }

    private function purgeGoogleCloudCache(): bool
    {
        // This would use Google Cloud SDK in a real implementation
        return true;
    }

    /**
     * Get CDN statistics.
     */
    /**
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        try {
            return match ($this->provider) {
                'cloudflare' => $this->getCloudflareStatistics(),
                'aws_s3' => $this->getS3Statistics(),
                'google_cloud' => $this->getGoogleCloudStatistics(),
                default => [],
            };
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
    /**
     * @return array<string, mixed>
     */
    private function getCloudflareStatistics(): array
    {
        $apiToken = is_string($this->config['api_token'] ?? null) ? $this->config['api_token'] : '';
        $zoneId = is_string($this->config['zone_id'] ?? null) ? $this->config['zone_id'] : '';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiToken,
        ])->get('https://api.cloudflare.com/client/v4/zones/'.$zoneId.'/analytics/dashboard');

        if (! $response->successful()) {
            throw new Exception('Failed to get Cloudflare statistics');
        }

        $result = $response->json();

        return is_array($result) ? $result : [];
    }

    /**
     * Get S3 statistics.
     */
    /**
     * @return array<string, mixed>
     */
    private function getS3Statistics(): array
    {
        // This would use AWS SDK in a real implementation
        return [];
    }

    /**
     * Get Google Cloud statistics.
     */
    /**
     * @return array<string, mixed>
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
            $testContent = 'CDN connection test - '.now();

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
