<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    public function cacheProductRecommendations(int $userId, array $recommendations, int $ttl = 3600): void
    {
        $key = "product_recommendations_user_{$userId}";
        Cache::put($key, $recommendations, $ttl);
    }

    public function getCachedRecommendations(int $userId): ?array
    {
        $key = "product_recommendations_user_{$userId}";

        return Cache::get($key);
    }

    public function cacheUserAnalytics(int $userId, array $analytics, int $ttl = 1800): void
    {
        $key = "user_analytics_{$userId}";
        Cache::put($key, $analytics, $ttl);
    }

    public function getCachedUserAnalytics(int $userId): ?array
    {
        $key = "user_analytics_{$userId}";

        return Cache::get($key);
    }

    public function cacheSiteAnalytics(array $analytics, int $ttl = 3600): void
    {
        Cache::put('site_analytics', $analytics, $ttl);
    }

    public function getCachedSiteAnalytics(): ?array
    {
        return Cache::get('site_analytics');
    }

    public function cacheProductDetails(int $productId, array $details, int $ttl = 7200): void
    {
        $key = "product_details_{$productId}";
        Cache::put($key, $details, $ttl);
    }

    public function getCachedProductDetails(int $productId): ?array
    {
        $key = "product_details_{$productId}";

        return Cache::get($key);
    }

    public function invalidateUserCache(int $userId): void
    {
        $keys = [
            "product_recommendations_user_{$userId}",
            "user_analytics_{$userId}",
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    public function invalidateProductCache(int $productId): void
    {
        Cache::forget("product_details_{$productId}");
    }

    public function warmUpCache(): void
    {
        // Warm up frequently accessed data
        $this->warmUpSiteAnalytics();
        $this->warmUpPopularProducts();
    }

    private function warmUpSiteAnalytics(): void
    {
        // This would typically call the analytics service
        // and cache the results
    }

    private function warmUpPopularProducts(): void
    {
        // Cache popular products for faster loading
    }

    public function getCacheStats(): array
    {
        if (config('cache.default') === 'redis') {
            $redis = Redis::connection();
            $info = $redis->info();

            return [
                'used_memory' => $info['used_memory_human'] ?? 'N/A',
                'connected_clients' => $info['connected_clients'] ?? 'N/A',
                'total_commands_processed' => $info['total_commands_processed'] ?? 'N/A',
            ];
        }

        return ['driver' => config('cache.default')];
    }

    public function remember(string $key, int $ttl, callable $callback, array $tags = []): mixed
    {
        $prefixedKey = 'coprra_cache_'.$key;

        try {
            if (! empty($tags)) {
                return Cache::tags($tags)->remember($prefixedKey, $ttl, function () use ($callback, $key) {
                    Log::debug('Cache miss - data generated', ['key' => $key, 'execution_time' => microtime(true)]);

                    return $callback();
                });
            }

            // Check if we're in a test environment with mocked facade
            $cache = Cache::getFacadeRoot();
            if (method_exists($cache, 'remember')) {
                return $cache->remember($prefixedKey, $ttl, function () use ($callback, $key) {
                    Log::debug('Cache miss - data generated', ['key' => $key, 'execution_time' => microtime(true)]);

                    return $callback();
                });
            }

            return Cache::remember($prefixedKey, $ttl, function () use ($callback, $key) {
                Log::debug('Cache miss - data generated', ['key' => $key, 'execution_time' => microtime(true)]);

                return $callback();
            });
        } catch (\Exception $e) {
            // If cache fails, execute callback directly
            return $callback();
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $prefixedKey = 'coprra_cache_'.$key;

        return Cache::get($prefixedKey, $default);
    }

    public function forget(string $key): bool
    {
        $prefixedKey = 'coprra_cache_'.$key;

        try {
            return Cache::forget($prefixedKey);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function forgetByTags(array $tags): bool
    {
        try {
            $cache = Cache::getFacadeRoot();
            $store = $cache->getStore();

            if (method_exists($store, 'tags')) {
                $taggedCache = $cache->tags($tags);
                $taggedCache->flush();

                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
