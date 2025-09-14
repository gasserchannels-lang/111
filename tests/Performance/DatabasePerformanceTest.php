<?php

namespace Tests\Performance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DatabasePerformanceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function simple_select_queries_perform_within_acceptable_time()
    {
        $startTime = microtime(true);

        $products = \App\Models\Product::all();

        $endTime = microtime(true);
        $queryTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(100, $queryTime); // Should complete within 100ms
    }

    #[Test]
    public function complex_join_queries_perform_within_acceptable_time()
    {
        $startTime = microtime(true);

        $products = \App\Models\Product::with(['category', 'brand', 'reviews'])
            ->where('is_active', true)
            ->where('price', '>', 100)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $endTime = microtime(true);
        $queryTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(500, $queryTime); // Should complete within 500ms
    }

    #[Test]
    public function search_queries_perform_within_acceptable_time()
    {
        $searchTerms = ['laptop', 'phone', 'clothing', 'electronics'];

        foreach ($searchTerms as $term) {
            $startTime = microtime(true);

            $products = \App\Models\Product::where('name', 'like', '%'.$term.'%')
                ->orWhere('description', 'like', '%'.$term.'%')
                ->get();

            $endTime = microtime(true);
            $queryTime = ($endTime - $startTime) * 1000;

            $this->assertLessThan(300, $queryTime); // Should complete within 300ms
        }
    }

    #[Test]
    public function aggregate_queries_perform_within_acceptable_time()
    {
        $startTime = microtime(true);

        $stats = \App\Models\Product::selectRaw('
            COUNT(*) as total_products,
            AVG(price) as average_price,
            MIN(price) as min_price,
            MAX(price) as max_price
        ')->first();

        $endTime = microtime(true);
        $queryTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(200, $queryTime); // Should complete within 200ms
    }

    #[Test]
    public function pagination_queries_perform_within_acceptable_time()
    {
        $startTime = microtime(true);

        $products = \App\Models\Product::paginate(20);

        $endTime = microtime(true);
        $queryTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(150, $queryTime); // Should complete within 150ms
    }

    #[Test]
    public function bulk_insert_operations_perform_within_acceptable_time()
    {
        $startTime = microtime(true);

        $products = \App\Models\Product::factory()->count(1000)->make();
        \App\Models\Product::insert($products->toArray());

        $endTime = microtime(true);
        $queryTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(2000, $queryTime); // Should complete within 2 seconds
    }

    #[Test]
    public function bulk_update_operations_perform_within_acceptable_time()
    {
        // Create test data
        \App\Models\Product::factory()->count(500)->create();

        $startTime = microtime(true);

        \App\Models\Product::where('price', '<', 100)
            ->update(['is_active' => false]);

        $endTime = microtime(true);
        $queryTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(1000, $queryTime); // Should complete within 1 second
    }

    #[Test]
    public function bulk_delete_operations_perform_within_acceptable_time()
    {
        // Create test data
        \App\Models\Product::factory()->count(500)->create();

        $startTime = microtime(true);

        \App\Models\Product::where('is_active', false)->delete();

        $endTime = microtime(true);
        $queryTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(1000, $queryTime); // Should complete within 1 second
    }

    #[Test]
    public function database_connections_are_efficient()
    {
        $startTime = microtime(true);

        // Test multiple database connections
        for ($i = 0; $i < 10; $i++) {
            $products = \App\Models\Product::all();
        }

        $endTime = microtime(true);
        $queryTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(500, $queryTime); // Should complete within 500ms
    }

    #[Test]
    public function database_transactions_perform_within_acceptable_time()
    {
        $startTime = microtime(true);

        DB::transaction(function () {
            $product = \App\Models\Product::factory()->create();
            $product->update(['name' => 'Updated Product']);
            $product->delete();
        });

        $endTime = microtime(true);
        $queryTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(300, $queryTime); // Should complete within 300ms
    }

    #[Test]
    public function database_indexes_improve_query_performance()
    {
        // Create test data
        \App\Models\Product::factory()->count(1000)->create();

        // Test query without index
        $startTime = microtime(true);
        $products = \App\Models\Product::where('name', 'like', '%test%')->get();
        $endTime = microtime(true);
        $queryTimeWithoutIndex = ($endTime - $startTime) * 1000;

        // Test query with index (assuming index exists)
        $startTime = microtime(true);
        $products = \App\Models\Product::where('is_active', true)->get();
        $endTime = microtime(true);
        $queryTimeWithIndex = ($endTime - $startTime) * 1000;

        // Query with index should be faster
        $this->assertLessThan($queryTimeWithoutIndex, $queryTimeWithIndex);
    }

    #[Test]
    public function database_queries_scale_linearly_with_data_size()
    {
        $dataSizes = [100, 500, 1000, 2000];
        $queryTimes = [];

        foreach ($dataSizes as $size) {
            // Create test data
            \App\Models\Product::factory()->count($size)->create();

            $startTime = microtime(true);

            $products = \App\Models\Product::all();

            $endTime = microtime(true);
            $queryTime = ($endTime - $startTime) * 1000;

            $queryTimes[] = $queryTime;
        }

        // Check that query time scales linearly
        $firstTime = $queryTimes[0];
        $lastTime = $queryTimes[count($queryTimes) - 1];
        $expectedRatio = $dataSizes[count($dataSizes) - 1] / $dataSizes[0];
        $actualRatio = $lastTime / $firstTime;

        $this->assertLessThan(2, abs($expectedRatio - $actualRatio)); // Should be roughly linear
    }
}
