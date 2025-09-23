<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    public function __construct()
    {
        // Constructor for future configuration if needed
    }

    /**
     * Cache data with automatic invalidation.
     *
     * @param  array<string>  $tags
     */
    public function remember(string $key, int $ttl, callable $callback, array $tags = []): mixed
    {
        try {
            $cacheKey = $this->buildCacheKey($key);
            $cache = Cache::getFacadeRoot();

            if ($tags !== [] && is_object($cache) && method_exists($cache, 'getStore')) {
                $store = $cache->getStore();
                if (is_object($store) && method_exists($store, 'tags') && method_exists($cache, 'tags')) {
                    $cache = $cache->tags($tags);
                }
            }

            if (is_object($cache) && method_exists($cache, 'remember')) {
                return $cache->remember($cacheKey, $ttl, function () use ($callback, $key) {
                    $startTime = microtime(true);
                    $result = $callback();
                    $executionTime = microtime(true) - $startTime;

                    Log::debug('Cache miss - data generated', [
                        'key' => $key,
                        'execution_time' => $executionTime,
                    ]);

                    return $result;
                });
            }

            // Fallback to callback execution if cache is not available
            return $callback();
        } catch (Exception $e) {
            Log::error('Cache error', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            // Fallback to callback execution
            return $callback();
        }
    }

    /**
     * Cache data permanently until manually invalidated.
     *
     * @param  array<string>  $tags
     */
    public function forever(string $key, mixed $value, array $tags = []): bool
    {
        try {
            $cacheKey = $this->buildCacheKey($key);
            $cache = Cache::getFacadeRoot();

            if ($tags !== [] && is_object($cache) && method_exists($cache, 'getStore')) {
                $store = $cache->getStore();
                if (is_object($store) && method_exists($store, 'tags') && method_exists($cache, 'tags')) {
                    $cache = $cache->tags($tags);
                }
            }

            if (is_object($cache) && method_exists($cache, 'forever')) {
                return $cache->forever($cacheKey, $value);
            }

            return false;
        } catch (Exception $e) {
            Log::error('Cache forever error', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get cached data.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        try {
            $cacheKey = $this->buildCacheKey($key);

            return Cache::get($cacheKey, $default);
        } catch (Exception $e) {
            Log::error('Cache get error', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return $default;
        }
    }

    /**
     * Store data in cache.
     *
     * @param  array<string>  $tags
     */
    public function put(string $key, mixed $value, int $ttl = 3600, array $tags = []): bool
    {
        try {
            $cacheKey = $this->buildCacheKey($key);
            $cache = Cache::getFacadeRoot();

            if ($tags !== [] && is_object($cache) && method_exists($cache, 'getStore')) {
                $store = $cache->getStore();
                if (is_object($store) && method_exists($store, 'tags') && method_exists($cache, 'tags')) {
                    $cache = $cache->tags($tags);
                }
            }

            if (is_object($cache) && method_exists($cache, 'put')) {
                return $cache->put($cacheKey, $value, $ttl);
            }

            return false;
        } catch (Exception $e) {
            Log::error('Cache put error', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Invalidate cache by key.
     */
    public function forget(string $key): bool
    {
        try {
            $cacheKey = $this->buildCacheKey($key);

            return Cache::forget($cacheKey);
        } catch (Exception $e) {
            Log::error('Cache forget error', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Invalidate cache by tags.
     *
     * @param  array<string>  $tags
     */
    public function forgetByTags(array $tags): bool
    {
        try {
            $cache = Cache::getFacadeRoot();
            if (is_object($cache) && method_exists($cache, 'getStore') && method_exists($cache->getStore(), 'tags') && method_exists($cache, 'tags')) {
                $taggedCache = $cache->tags($tags);
                if (is_object($taggedCache) && method_exists($taggedCache, 'flush')) {
                    return $taggedCache->flush();
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error('Cache forget by tags error', [
                'tags' => $tags,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Clear all cache.
     */
    public function flush(): bool
    {
        try {
            return Cache::flush();
        } catch (Exception $e) {
            Log::error('Cache flush error', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Cache model data with automatic invalidation.
     */
    public function rememberModel(string $key, Model $model, int $ttl = 3600): mixed
    {
        $tags = $this->getModelTags($model);

        return $this->remember($key, $ttl, fn () => $model->toArray(), $tags);
    }

    /**
     * Cache query results.
     */
    /**
     * @param  list<string>  $tags
     */
    public function rememberQuery(string $key, callable $query, int $ttl = 1800, array $tags = []): mixed
    {
        return $this->remember($key, $ttl, $query, $tags);
    }

    /**
     * Cache API responses.
     */
    /**
     * @param  array<string, mixed>  $params
     */
    public function rememberApiResponse(string $endpoint, array $params, callable $callback, int $ttl = 300): mixed
    {
        $key = 'api:'.$endpoint.':'.hash('sha256', serialize($params));
        $tags = ['api', $endpoint];

        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Cache search results.
     */
    /**
     * @param  array<string, mixed>  $filters
     */
    public function rememberSearch(string $query, array $filters, callable $callback, int $ttl = 600): mixed
    {
        $key = 'search:'.hash('sha256', $query.serialize($filters));
        $tags = ['search', 'products'];

        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Cache product data.
     */
    public function rememberProduct(int $productId, callable $callback, int $ttl = 1800): mixed
    {
        $key = "product:{$productId}";
        $tags = ['products', "product:{$productId}"];

        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Cache category data.
     */
    public function rememberCategory(int $categoryId, callable $callback, int $ttl = 3600): mixed
    {
        $key = "category:{$categoryId}";
        $tags = ['categories', "category:{$categoryId}"];

        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Cache brand data.
     */
    public function rememberBrand(int $brandId, callable $callback, int $ttl = 3600): mixed
    {
        $key = "brand:{$brandId}";
        $tags = ['brands', "brand:{$brandId}"];

        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Cache user data.
     */
    public function rememberUser(int $userId, callable $callback, int $ttl = 1800): mixed
    {
        $key = "user:{$userId}";
        $tags = ['users', "user:{$userId}"];

        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Cache statistics.
     */
    public function rememberStats(string $statType, callable $callback, int $ttl = 300): mixed
    {
        $key = "stats:{$statType}";
        $tags = ['statistics', $statType];

        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Cache configuration data.
     */
    public function rememberConfig(string $configKey, callable $callback, int $ttl = 86400): mixed
    {
        $key = "config:{$configKey}";
        $tags = ['config'];

        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Warm up cache.
     */
    /**
     * @param  array<string, callable>  $keys
     * @return array<string, mixed>
     */
    public function warmUp(array $keys): array
    {
        $results = [];

        foreach ($keys as $key => $callback) {
            try {
                $results[$key] = $this->remember($key, 3600, $callback);
            } catch (Exception $e) {
                $results[$key] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Get cache statistics.
     *
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        try {
            $driver = config('cache.default');
            $store = Cache::store();

            return [
                'driver' => $driver,
                'store' => $store::class,
                'memory_usage' => memory_get_usage(true),
                'peak_memory' => memory_get_peak_usage(true),
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build cache key with prefix.
     */
    private function buildCacheKey(string $key): string
    {
        $prefix = config('cache.prefix', 'coprra_cache');

        $prefixValue = is_string($prefix) ? $prefix : 'coprra_cache';

        return $prefixValue.":{$key}";
    }

    /**
     * Get model tags for cache invalidation.
     *
     *
     * @return array<string>
     */
    private function getModelTags(Model $model): array
    {
        $tags = [];
        $modelClass = $model::class;

        // Add model type tag
        $tags[] = strtolower(class_basename($modelClass));

        // Add specific model tag
        $modelKey = $model->getKey();
        if (is_string($modelKey)) {
            $tags[] = strtolower(class_basename($modelClass)).':'.$modelKey;
        } else {
            $modelKeyString = is_scalar($modelKey) ? (string) $modelKey : '';
            $tags[] = strtolower(class_basename($modelClass)).':'.$modelKeyString;
        }

        // Add related model tags
        foreach ($model->getRelations() as $related) {
            if ($related instanceof Model) {
                $relatedKey = $related->getKey();
                if (is_string($relatedKey)) {
                    $tags[] = strtolower(class_basename($related::class)).':'.$relatedKey;
                } else {
                    $relatedKeyString = is_scalar($relatedKey) ? (string) $relatedKey : '';
                    $tags[] = strtolower(class_basename($related::class)).':'.$relatedKeyString;
                }
            }
        }

        return $tags;
    }

    /**
     * Cache with automatic model invalidation.
     */
    public function rememberWithModelInvalidation(string $key, Model $model, callable $callback, int $ttl = 3600): mixed
    {
        $tags = $this->getModelTags($model);

        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Invalidate cache when model is updated.
     */
    public function invalidateModelCache(Model $model): void
    {
        $tags = $this->getModelTags($model);
        $this->forgetByTags($tags);

        Log::info('Model cache invalidated', [
            'model' => $model::class,
            'id' => $model->getKey(),
            'tags' => $tags,
        ]);
    }

    /**
     * Cache with lock to prevent cache stampede.
     */
    public function rememberWithLock(string $key, int $ttl, callable $callback, int $lockTimeout = 10): mixed
    {
        $lockKey = "lock:{$key}";
        $lock = Cache::lock($lockKey, $lockTimeout);

        if ($lock->get()) {
            try {
                return $this->remember($key, $ttl, $callback);
            } finally {
                $lock->release();
            }
        }

        // If lock couldn't be acquired, wait and try to get cached value
        usleep(100000); // Wait 100ms

        return $this->get($key);
    }

    /**
     * Cache with compression for large data.
     */
    public function rememberCompressed(string $key, mixed $value, int $ttl = 3600): mixed
    {
        try {
            $compressed = gzcompress(serialize($value), 6);
            $this->put($key, $compressed, $ttl);

            return $value;
        } catch (Exception $e) {
            Log::error('Cache compression error', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return $this->put($key, $value, $ttl);
        }
    }

    /**
     * Get compressed cache data.
     */
    public function getCompressed(string $key, mixed $default = null): mixed
    {
        try {
            $compressed = $this->get($key);

            if ($compressed === null) {
                return $default;
            }

            if (is_string($compressed)) {
                $decompressed = gzuncompress($compressed);
            } else {
                $compressedString = is_scalar($compressed) ? (string) $compressed : '';
                $decompressed = gzuncompress($compressedString);
            }
            if ($decompressed === false) {
                return $default;
            }

            return unserialize($decompressed);
        } catch (Exception $e) {
            Log::error('Cache decompression error', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return $default;
        }
    }
}
