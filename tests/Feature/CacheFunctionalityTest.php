<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CacheFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_store_data_in_cache()
    {
        Cache::put('test_key', 'test_value', 60);
        $this->assertEquals('test_value', Cache::get('test_key'));
    }

    #[Test]
    public function cache_expires_after_ttl()
    {
        Cache::put('expiring_key', 'expiring_value', 1);

        $this->assertEquals('expiring_value', Cache::get('expiring_key'));

        // Wait for expiration
        sleep(2);

        $this->assertNull(Cache::get('expiring_key'));
    }

    #[Test]
    public function can_remember_cached_data()
    {
        $value = Cache::remember('remember_key', 60, function () {
            return 'computed_value';
        });

        $this->assertEquals('computed_value', $value);
        $this->assertEquals('computed_value', Cache::get('remember_key'));
    }

    #[Test]
    public function can_forget_cached_data()
    {
        Cache::put('forget_key', 'forget_value', 60);
        $this->assertEquals('forget_value', Cache::get('forget_key'));

        Cache::forget('forget_key');
        $this->assertNull(Cache::get('forget_key'));
    }

    #[Test]
    public function can_clear_all_cache()
    {
        Cache::put('key1', 'value1', 60);
        Cache::put('key2', 'value2', 60);

        $this->assertEquals('value1', Cache::get('key1'));
        $this->assertEquals('value2', Cache::get('key2'));

        Cache::flush();

        $this->assertNull(Cache::get('key1'));
        $this->assertNull(Cache::get('key2'));
    }

    #[Test]
    public function cache_improves_performance()
    {
        $startTime = microtime(true);

        // First call - should be slow
        $result1 = $this->get('/api/expensive-operation');

        $firstCallTime = microtime(true) - $startTime;

        $startTime = microtime(true);

        // Second call - should be fast (cached)
        $result2 = $this->get('/api/expensive-operation');

        $secondCallTime = microtime(true) - $startTime;

        $this->assertLessThan($firstCallTime, $secondCallTime);
    }
}
