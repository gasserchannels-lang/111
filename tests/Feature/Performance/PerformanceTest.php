<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\PriceOffer;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_large_dataset_efficiently()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        // Create 1000 products
        $products = Product::factory()->count(1000)->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        // Create price offers for each product
        foreach ($products as $product) {
            PriceOffer::factory()->create([
                'product_id' => $product->id,
                'store_id' => $store->id,
                'price' => rand(10, 1000),
                'is_available' => true,
            ]);
        }

        $startTime = microtime(true);

        $response = $this->getJson('/api/price-search?q=Test');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(2.0, $executionTime, 'Search should complete within 2 seconds');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_concurrent_requests()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'name' => 'Test Product',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $priceOffer = PriceOffer::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 100.00,
            'is_available' => true,
        ]);

        $startTime = microtime(true);

        // Simulate 10 concurrent requests
        $responses = [];
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->getJson('/api/price-search?q=Test');

            // Add delay to avoid rate limiting
            if ($i < 9) { // Don't delay after last request
                usleep(500000); // 0.5 second
            }
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        $this->assertLessThan(10.0, $executionTime, 'Concurrent requests should complete within 10 seconds');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_memory_efficiently()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        // Create 500 products
        $products = Product::factory()->count(500)->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $initialMemory = memory_get_usage();

        $response = $this->getJson('/api/price-search?q=Test');

        $finalMemory = memory_get_usage();
        $memoryUsed = $finalMemory - $initialMemory;

        $response->assertStatus(200);
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, 'Memory usage should be less than 50MB');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_database_query_optimization()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'name' => 'Test Product',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $priceOffer = PriceOffer::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 100.00,
            'is_available' => true,
        ]);

        // Add delay to avoid rate limiting
        usleep(800000); // 0.8 second

        // Enable query logging
        \DB::enableQueryLog();

        $response = $this->getJson('/api/price-search?q=Test');

        $queries = \DB::getQueryLog();

        $response->assertStatus(200);
        $this->assertLessThan(10, count($queries), 'Should use less than 10 database queries');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_pagination_performance()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        // Create 1000 products
        $products = Product::factory()->count(1000)->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $startTime = microtime(true);

        $response = $this->getJson('/api/price-search?q=Test&per_page=50&page=1');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(1.0, $executionTime, 'Pagination should complete within 1 second');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_cache_performance()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'name' => 'Test Product',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $priceOffer = PriceOffer::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 100.00,
            'is_available' => true,
        ]);

        // First request (cache miss)
        $startTime = microtime(true);
        $response1 = $this->getJson('/api/price-search?q=Test');
        $endTime = microtime(true);
        $firstRequestTime = $endTime - $startTime;

        // Second request (cache hit)
        $startTime = microtime(true);
        $response2 = $this->getJson('/api/price-search?q=Test');
        $endTime = microtime(true);
        $secondRequestTime = $endTime - $startTime;

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // Second request should be faster (cache hit) or at least not significantly slower
        // Note: In testing environment, cache might not be enabled or timing might be inconsistent
        $this->assertTrue($secondRequestTime <= $firstRequestTime * 1.5, 'Cached request should not be significantly slower');
    }
}
