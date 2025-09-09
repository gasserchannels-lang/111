<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Exception;

class CacheService
{
    private array $config;
    private array $tags = [];

    public function __construct()
    {
        $this->config = config('cache', []);
    }

    /**
     * Cache data with automatic invalidation
     */
    public function remember(string $key, int $ttl, callable $callback, array $tags = []): mixed
    {
        try {
            $cacheKey = $this->buildCacheKey($key);
            
            return Cache::tags($tags)->remember($cacheKey, $ttl, function () use ($callback) {
                $startTime = microtime(true);
                $result = $callback();
                $executionTime = microtime(true) - $startTime;
                
                Log::debug('Cache miss - data generated', [
                    'key' => $key,
                    'execution_time' => $executionTime,
                ]);
                
                return $result;
            });
            
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
     * Cache data permanently until manually invalidated
     */
    public function forever(string $key, mixed $value, array $tags = []): bool
    {
        try {
            $cacheKey = $this->buildCacheKey($key);
            return Cache::tags($tags)->forever($cacheKey, $value);
            
        } catch (Exception $e) {
            Log::error('Cache forever error', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Get cached data
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
     * Store data in cache
     */
    public function put(string $key, mixed $value, int $ttl = 3600, array $tags = []): bool
    {
        try {
            $cacheKey = $this->buildCacheKey($key);
            return Cache::tags($tags)->put($cacheKey, $value, $ttl);
            
        } catch (Exception $e) {
            Log::error('Cache put error', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Invalidate cache by key
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
     * Invalidate cache by tags
     */
    public function forgetByTags(array $tags): bool
    {
        try {
            return Cache::tags($tags)->flush();
            
        } catch (Exception $e) {
            Log::error('Cache forget by tags error', [
                'tags' => $tags,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Clear all cache
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
     * Cache model data with automatic invalidation
     */
    public function rememberModel(string $key, Model $model, int $ttl = 3600): mixed
    {
        $tags = $this->getModelTags($model);
        
        return $this->remember($key, $ttl, function () use ($model) {
            return $model->toArray();
        }, $tags);
    }

    /**
     * Cache query results
     */
    public function rememberQuery(string $key, callable $query, int $ttl = 1800, array $tags = []): mixed
    {
        return $this->remember($key, $ttl, $query, $tags);
    }

    /**
     * Cache API responses
     */
    public function rememberApiResponse(string $endpoint, array $params, callable $callback, int $ttl = 300): mixed
    {
        $key = 'api:' . $endpoint . ':' . md5(serialize($params));
        $tags = ['api', $endpoint];
        
        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Cache search results
     */
    public function rememberSearch(string $query, array $filters, callable $callback, int $ttl = 600): mixed
    {
        $key = 'search:' . md5($query . serialize($filters));
        $tags = ['search', 'products'];
        
        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Cache product data
     */
    public function rememberProduct(int $productId, callable $callback, int $ttl = 1800): mixed
    {
        $key = "product:{$productId}";
        $tags = ['products', "product:{$productId}"];
        
        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Cache category data
     */
    public function rememberCategory(int $categoryId, callable $callback, int $ttl = 3600): mixed
    {
        $key = "category:{$categoryId}";
        $tags = ['categories', "category:{$categoryId}"];
        
        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Cache brand data
     */
    public function rememberBrand(int $brandId, callable $callback, int $ttl = 3600): mixed
    {
        $key = "brand:{$brandId}";
        $tags = ['brands', "brand:{$brandId}"];
        
        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Cache user data
     */
    public function rememberUser(int $userId, callable $callback, int $ttl = 1800): mixed
    {
        $key = "user:{$userId}";
        $tags = ['users', "user:{$userId}"];
        
        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Cache statistics
     */
    public function rememberStats(string $statType, callable $callback, int $ttl = 300): mixed
    {
        $key = "stats:{$statType}";
        $tags = ['statistics', $statType];
        
        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Cache configuration data
     */
    public function rememberConfig(string $configKey, callable $callback, int $ttl = 86400): mixed
    {
        $key = "config:{$configKey}";
        $tags = ['config'];
        
        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Warm up cache
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
     * Get cache statistics
     */
    public function getStats(): array
    {
        try {
            $driver = config('cache.default');
            $store = Cache::store();
            
            return [
                'driver' => $driver,
                'store' => get_class($store),
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
     * Build cache key with prefix
     */
    private function buildCacheKey(string $key): string
    {
        $prefix = config('cache.prefix', 'coprra_cache');
        return "{$prefix}:{$key}";
    }

    /**
     * Get model tags for cache invalidation
     */
    private function getModelTags(Model $model): array
    {
        $tags = [];
        $modelClass = get_class($model);
        
        // Add model type tag
        $tags[] = strtolower(class_basename($modelClass));
        
        // Add specific model tag
        $tags[] = strtolower(class_basename($modelClass)) . ':' . $model->getKey();
        
        // Add related model tags
        foreach ($model->getRelations() as $relation => $related) {
            if ($related instanceof Model) {
                $tags[] = strtolower(class_basename(get_class($related))) . ':' . $related->getKey();
            }
        }
        
        return $tags;
    }

    /**
     * Cache with automatic model invalidation
     */
    public function rememberWithModelInvalidation(string $key, Model $model, callable $callback, int $ttl = 3600): mixed
    {
        $tags = $this->getModelTags($model);
        
        return $this->remember($key, $ttl, $callback, $tags);
    }

    /**
     * Invalidate cache when model is updated
     */
    public function invalidateModelCache(Model $model): void
    {
        $tags = $this->getModelTags($model);
        $this->forgetByTags($tags);
        
        Log::info('Model cache invalidated', [
            'model' => get_class($model),
            'id' => $model->getKey(),
            'tags' => $tags,
        ]);
    }

    /**
     * Cache with lock to prevent cache stampede
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
     * Cache with compression for large data
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
     * Get compressed cache data
     */
    public function getCompressed(string $key, mixed $default = null): mixed
    {
        try {
            $compressed = $this->get($key);
            
            if ($compressed === null) {
                return $default;
            }
            
            $decompressed = gzuncompress($compressed);
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
