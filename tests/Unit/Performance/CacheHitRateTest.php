<?php

namespace Tests\Unit\Performance;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CacheHitRateTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_measures_cache_hit_rate(): void
    {
        $cache = $this->createCache();
        $keys = ['product_1', 'product_2', 'product_3', 'product_4', 'product_5'];

        // First access (cache miss) - but also set cache
        foreach ($keys as $key) {
            $this->setCache($cache, $key, "value_$key");
            $this->getFromCache($cache, $key);
        }

        // Second access (cache hit)
        foreach ($keys as $key) {
            $this->getFromCache($cache, $key);
        }

        $hitRate = $this->calculateCacheHitRate($cache);
        $this->assertGreaterThan(0.8, $hitRate); // Should have at least 80% hit rate
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_cache_miss_rate(): void
    {
        $cache = $this->createCache();
        $keys = ['product_1', 'product_2', 'product_3', 'product_4', 'product_5'];

        // Access each key once (all misses)
        foreach ($keys as $key) {
            $this->getFromCache($cache, $key);
        }

        $missRate = $this->calculateCacheMissRate($cache);
        $this->assertEquals(1.0, $missRate); // Should have 100% miss rate
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_cache_eviction_rate(): void
    {
        $cache = $this->createCacheWithLimit(3);
        $keys = ['product_1', 'product_2', 'product_3', 'product_4', 'product_5'];

        // Fill cache beyond limit and access to generate hits/misses
        foreach ($keys as $key) {
            $this->setCache($cache, $key, "value_$key");
            $this->getFromCache($cache, $key); // Generate a hit
        }

        $evictionRate = $this->calculateCacheEvictionRate($cache);
        $this->assertGreaterThan(0, $evictionRate); // Should have some evictions
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_cache_ttl_effectiveness(): void
    {
        $cache = $this->createCacheWithTTL(1); // 1 second TTL
        $key = 'product_1';

        // Set cache with TTL
        $this->setCache($cache, $key, 'value_1');

        // Access immediately (should hit)
        $hit1 = $this->getFromCache($cache, $key);
        $this->assertTrue($hit1);

        // Wait for TTL to expire
        sleep(2);

        // Access after TTL (should miss)
        $hit2 = $this->getFromCache($cache, $key);
        $this->assertFalse($hit2);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_cache_warmup_performance(): void
    {
        $cache = $this->createCache();
        $keys = ['product_1', 'product_2', 'product_3', 'product_4', 'product_5'];

        $startTime = microtime(true);

        $this->warmupCache($cache, $keys);

        $endTime = microtime(true);
        $warmupTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(100, $warmupTime); // Should warmup in under 100ms
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_cache_clear_performance(): void
    {
        $cache = $this->createCache();
        $keys = ['product_1', 'product_2', 'product_3', 'product_4', 'product_5'];

        // Fill cache
        foreach ($keys as $key) {
            $this->setCache($cache, $key, "value_$key");
        }

        $startTime = microtime(true);

        $this->clearCache($cache);

        $endTime = microtime(true);
        $clearTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(50, $clearTime); // Should clear in under 50ms
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_cache_memory_usage(): void
    {
        $cache = $this->createCache();
        $keys = ['product_1', 'product_2', 'product_3', 'product_4', 'product_5'];

        $memoryBefore = memory_get_usage();

        // Fill cache
        foreach ($keys as $key) {
            $this->setCache($cache, $key, "value_$key");
        }

        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;

        $this->assertLessThan(1024 * 1024, $memoryUsed); // Should use less than 1MB
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_cache_compression_effectiveness(): void
    {
        $cache = $this->createCache();
        $key = 'large_data';
        $largeData = str_repeat('This is a large data string. ', 1000);

        // Store without compression
        $this->setCache($cache, $key, $largeData, false);
        $uncompressedSize = $this->getCacheSize($cache, $key);

        // Clear cache
        $this->clearCache($cache);

        // Store with compression
        $this->setCache($cache, $key, $largeData, true);
        $compressedSize = $this->getCacheSize($cache, $key);

        $compressionRatio = $compressedSize / $uncompressedSize;
        $this->assertLessThan(0.5, $compressionRatio); // Should compress to less than 50% of original size
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_cache_distributed_performance(): void
    {
        $cache1 = $this->createCache();
        $cache2 = $this->createCache();
        $key = 'product_1';
        $value = 'value_1';

        // Set in first cache
        $this->setCache($cache1, $key, $value);

        // Replicate to second cache
        $startTime = microtime(true);

        $this->replicateCache($cache1, $cache2);

        $endTime = microtime(true);
        $replicationTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(200, $replicationTime); // Should replicate in under 200ms

        // Verify replication
        $hit = $this->getFromCache($cache2, $key);
        $this->assertTrue($hit);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_cache_invalidation_performance(): void
    {
        $cache = $this->createCache();
        $keys = ['product_1', 'product_2', 'product_3', 'product_4', 'product_5'];

        // Fill cache
        foreach ($keys as $key) {
            $this->setCache($cache, $key, "value_$key");
        }

        $startTime = microtime(true);

        // Invalidate specific keys
        $this->invalidateCacheKeys($cache, ['product_1', 'product_3']);

        $endTime = microtime(true);
        $invalidationTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(50, $invalidationTime); // Should invalidate in under 50ms

        // Verify invalidation
        $this->assertFalse($this->getFromCache($cache, 'product_1'));
        $this->assertFalse($this->getFromCache($cache, 'product_3'));
        $this->assertTrue($this->getFromCache($cache, 'product_2'));
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_cache_pattern_effectiveness(): void
    {
        $cache = $this->createCache();
        $patterns = [
            'product_*' => ['product_1', 'product_2', 'product_3'],
            'user_*' => ['user_1', 'user_2', 'user_3'],
            'category_*' => ['category_1', 'category_2', 'category_3'],
        ];

        // Fill cache with different patterns
        foreach ($patterns as $pattern => $keys) {
            foreach ($keys as $key) {
                $this->setCache($cache, $key, "value_$key");
            }
        }

        // Test pattern-based operations
        $startTime = microtime(true);

        $this->invalidateCachePattern($cache, 'product_*');

        $endTime = microtime(true);
        $patternTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(100, $patternTime); // Should handle patterns in under 100ms

        // Verify pattern invalidation
        $this->assertFalse($this->getFromCache($cache, 'product_1'));
        $this->assertTrue($this->getFromCache($cache, 'user_1'));
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_cache_consistency(): void
    {
        $cache1 = $this->createCache();
        $cache2 = $this->createCache();
        $key = 'product_1';
        $value1 = 'value_1';
        $value2 = 'value_2';

        // Set different values in both caches
        $this->setCache($cache1, $key, $value1);
        $this->setCache($cache2, $key, $value2);

        // Synchronize caches
        $this->synchronizeCaches($cache1, $cache2);

        // Verify consistency
        $value1 = $this->getCacheValue($cache1, $key);
        $value2 = $this->getCacheValue($cache2, $key);
        $this->assertEquals($value1, $value2);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_cache_throughput(): void
    {
        $cache = $this->createCache();
        $operations = 1000;

        $startTime = microtime(true);

        for ($i = 0; $i < $operations; $i++) {
            $key = "product_$i";
            $value = "value_$i";

            if ($i % 2 === 0) {
                $this->setCache($cache, $key, $value);
            } else {
                $this->getFromCache($cache, $key);
            }
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        $throughput = $operations / $totalTime; // Operations per second

        $this->assertGreaterThan(1000, $throughput); // Should handle at least 1000 operations per second
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_cache_latency(): void
    {
        $cache = $this->createCache();
        $key = 'product_1';
        $value = 'value_1';

        // Set cache
        $this->setCache($cache, $key, $value);

        // Measure get latency
        $startTime = microtime(true);

        $this->getFromCache($cache, $key);

        $endTime = microtime(true);
        $latency = ($endTime - $startTime) * 1000;

        $this->assertLessThan(10, $latency); // Should be under 10ms
    }

    private function createCache(): array
    {
        return [
            'data' => [],
            'hits' => 0,
            'misses' => 0,
            'evictions' => 0,
            'size' => 0,
            'max_size' => 1000,
            'ttl' => 3600, // 1 hour default
        ];
    }

    private function createCacheWithLimit(int $limit): array
    {
        $cache = $this->createCache();
        $cache['max_size'] = $limit;

        return $cache;
    }

    private function createCacheWithTTL(int $ttl): array
    {
        $cache = $this->createCache();
        $cache['ttl'] = $ttl;

        return $cache;
    }

    private function getFromCache(array &$cache, string $key): bool
    {
        if (isset($cache['data'][$key])) {
            $item = $cache['data'][$key];

            // Check TTL
            if (time() - $item['timestamp'] > $cache['ttl']) {
                unset($cache['data'][$key]);
                $cache['misses']++;
                $cache['size']--;

                return false;
            }

            $cache['hits']++;

            return true;
        }

        $cache['misses']++;

        return false;
    }

    private function setCache(array &$cache, string $key, string $value, bool $compress = false): void
    {
        // Check if we need to evict
        if ($cache['size'] >= $cache['max_size']) {
            $this->evictCache($cache);
        }

        $cache['data'][$key] = [
            'value' => $compress ? gzcompress($value) : $value,
            'timestamp' => time(),
            'compressed' => $compress,
        ];

        $cache['size']++;
    }

    private function evictCache(array &$cache): void
    {
        if (empty($cache['data'])) {
            return;
        }

        // Simple LRU eviction
        $oldestKey = array_key_first($cache['data']);
        unset($cache['data'][$oldestKey]);
        $cache['size']--;
        $cache['evictions']++;
    }

    private function clearCache(array &$cache): void
    {
        $cache['data'] = [];
        $cache['size'] = 0;
    }

    private function calculateCacheHitRate(array $cache): float
    {
        $total = $cache['hits'] + $cache['misses'];

        return $total > 0 ? $cache['hits'] / $total : 0;
    }

    private function calculateCacheMissRate(array $cache): float
    {
        $total = $cache['hits'] + $cache['misses'];

        return $total > 0 ? $cache['misses'] / $total : 0;
    }

    private function calculateCacheEvictionRate(array $cache): float
    {
        $totalOperations = $cache['hits'] + $cache['misses'];

        return $totalOperations > 0 ? $cache['evictions'] / $totalOperations : 0;
    }

    private function warmupCache(array &$cache, array $keys): void
    {
        foreach ($keys as $key) {
            $this->setCache($cache, $key, "value_$key");
        }
    }

    private function getCacheSize(array $cache, string $key): int
    {
        if (isset($cache['data'][$key])) {
            return strlen($cache['data'][$key]['value']);
        }

        return 0;
    }

    private function replicateCache(array $source, array &$destination): void
    {
        $destination['data'] = $source['data'];
        $destination['size'] = $source['size'];
    }

    private function invalidateCacheKeys(array &$cache, array $keys): void
    {
        foreach ($keys as $key) {
            if (isset($cache['data'][$key])) {
                unset($cache['data'][$key]);
                $cache['size']--;
            }
        }
    }

    private function invalidateCachePattern(array &$cache, string $pattern): void
    {
        $pattern = str_replace('*', '.*', $pattern);
        $pattern = "/^$pattern$/";

        foreach ($cache['data'] as $key => $value) {
            if (preg_match($pattern, $key)) {
                unset($cache['data'][$key]);
                $cache['size']--;
            }
        }
    }

    private function synchronizeCaches(array &$cache1, array &$cache2): void
    {
        // Simple synchronization - use cache1 as source of truth
        $cache2['data'] = $cache1['data'];
        $cache2['size'] = $cache1['size'];
    }

    private function getCacheValue(array $cache, string $key): ?string
    {
        if (isset($cache['data'][$key])) {
            $item = $cache['data'][$key];

            return $item['compressed'] ? gzuncompress($item['value']) : $item['value'];
        }

        return null;
    }
}
