<?php

declare(strict_types=1);

namespace Tests\Benchmarks;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\PriceOffer;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PerformanceBenchmark extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Benchmark product search performance
     */
    public function test_product_search_performance()
    {
        // Create test data
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $store = Store::factory()->create();

        // Create 1000 products
        $products = Product::factory()->count(1000)->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);

        // Create price offers for each product
        foreach ($products as $product) {
            PriceOffer::factory()->create([
                'product_id' => $product->id,
                'store_id' => $store->id,
            ]);
        }

        $startTime = microtime(true);

        // Perform search
        $response = $this->getJson('/api/price-search?q=test');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);

        // Assert performance (should complete within 2 seconds)
        $this->assertLessThan(2.0, $executionTime, 'Product search should complete within 2 seconds');

        echo "\nProduct search with 1000 products completed in: " . round($executionTime, 4) . " seconds\n";
    }

    /**
     * Benchmark database query performance
     */
    public function test_database_query_performance()
    {
        // Create test data
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $store = Store::factory()->create();

        // Create 500 products with relationships
        $products = Product::factory()->count(500)->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);

        // Create price offers
        foreach ($products as $product) {
            PriceOffer::factory()->count(3)->create([
                'product_id' => $product->id,
                'store_id' => $store->id,
            ]);
        }

        $startTime = microtime(true);

        // Complex query with relationships
        $results = Product::with(['brand', 'category', 'priceOffers.store'])
            ->where('is_active', true)
            ->whereHas('priceOffers', function ($query) {
                $query->where('is_available', true);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertNotEmpty($results);

        // Assert performance (should complete within 1 second)
        $this->assertLessThan(1.0, $executionTime, 'Complex database query should complete within 1 second');

        echo "\nComplex database query with 500 products completed in: " . round($executionTime, 4) . " seconds\n";
    }

    /**
     * Benchmark memory usage
     */
    public function test_memory_usage()
    {
        $initialMemory = memory_get_usage();

        // Create large dataset
        $brands = Brand::factory()->count(100)->create();
        $categories = Category::factory()->count(50)->create();
        $stores = Store::factory()->count(20)->create();

        $products = collect();
        for ($i = 0; $i < 1000; $i++) {
            $products->push(Product::factory()->create([
                'brand_id' => $brands->random()->id,
                'category_id' => $categories->random()->id,
            ]));
        }

        $peakMemory = memory_get_peak_usage();
        $memoryUsed = $peakMemory - $initialMemory;
        $memoryUsedMB = $memoryUsed / 1024 / 1024;

        // Assert memory usage (should not exceed 50MB)
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, 'Memory usage should not exceed 50MB');

        echo "\nMemory usage for 1000 products: " . round($memoryUsedMB, 2) . " MB\n";
    }

    /**
     * Benchmark concurrent requests simulation
     */
    public function test_concurrent_requests_performance()
    {
        // Create test data
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $store = Store::factory()->create();

        Product::factory()->count(100)->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);

        $startTime = microtime(true);

        // Simulate 10 concurrent requests
        $responses = [];
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->getJson('/api/price-search?q=test');
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Verify all responses are successful
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        // Assert performance (10 concurrent requests should complete within 5 seconds)
        $this->assertLessThan(5.0, $executionTime, '10 concurrent requests should complete within 5 seconds');

        echo "\n10 concurrent requests completed in: " . round($executionTime, 4) . " seconds\n";
    }
}
