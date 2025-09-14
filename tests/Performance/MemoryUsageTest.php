<?php

namespace Tests\Performance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MemoryUsageTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function memory_usage_remains_within_acceptable_limits()
    {
        $initialMemory = memory_get_usage(true);

        // Perform memory-intensive operations
        $products = \App\Models\Product::factory()->count(1000)->create();

        $finalMemory = memory_get_usage(true);
        $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024; // Convert to MB

        $this->assertLessThan(100, $memoryUsed); // Should use less than 100MB
    }

    #[Test]
    public function large_dataset_processing_does_not_exceed_memory_limits()
    {
        $initialMemory = memory_get_usage(true);

        // Process large dataset
        $products = \App\Models\Product::factory()->count(5000)->create();

        foreach ($products as $product) {
            $product->update(['name' => $product->name.' Updated']);
        }

        $finalMemory = memory_get_usage(true);
        $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024;

        $this->assertLessThan(200, $memoryUsed); // Should use less than 200MB
    }

    #[Test]
    public function image_processing_does_not_exceed_memory_limits()
    {
        $initialMemory = memory_get_usage(true);

        // Process multiple images
        for ($i = 0; $i < 10; $i++) {
            $image = imagecreate(1000, 1000);
            $white = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $white);

            $imagePath = storage_path("app/test-image-{$i}.jpg");
            imagejpeg($image, $imagePath);
            imagedestroy($image);
        }

        $finalMemory = memory_get_usage(true);
        $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024;

        $this->assertLessThan(150, $memoryUsed); // Should use less than 150MB
    }

    #[Test]
    public function database_queries_do_not_cause_memory_leaks()
    {
        $initialMemory = memory_get_usage(true);

        // Perform multiple database queries
        for ($i = 0; $i < 100; $i++) {
            $products = \App\Models\Product::with(['category', 'brand'])
                ->where('is_active', true)
                ->get();
        }

        $finalMemory = memory_get_usage(true);
        $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024;

        $this->assertLessThan(50, $memoryUsed); // Should use less than 50MB
    }

    #[Test]
    public function api_requests_do_not_cause_memory_leaks()
    {
        $initialMemory = memory_get_usage(true);

        // Make multiple API requests
        for ($i = 0; $i < 50; $i++) {
            $response = $this->getJson('/api/products');
        }

        $finalMemory = memory_get_usage(true);
        $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024;

        $this->assertLessThan(30, $memoryUsed); // Should use less than 30MB
    }

    #[Test]
    public function file_operations_do_not_cause_memory_leaks()
    {
        $initialMemory = memory_get_usage(true);

        // Perform file operations
        for ($i = 0; $i < 100; $i++) {
            $file = \Illuminate\Http\UploadedFile::fake()->create('test.txt', 1000);
            $file->store('test');
        }

        $finalMemory = memory_get_usage(true);
        $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024;

        $this->assertLessThan(40, $memoryUsed); // Should use less than 40MB
    }

    #[Test]
    public function memory_usage_returns_to_normal_after_operations()
    {
        $initialMemory = memory_get_usage(true);

        // Perform memory-intensive operations
        $products = \App\Models\Product::factory()->count(1000)->create();

        // Clear memory
        unset($products);
        gc_collect_cycles();

        $finalMemory = memory_get_usage(true);
        $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024;

        $this->assertLessThan(20, $memoryUsed); // Should return to near initial state
    }

    #[Test]
    public function concurrent_requests_do_not_cause_memory_issues()
    {
        $initialMemory = memory_get_usage(true);

        // Simulate concurrent requests
        $responses = [];
        for ($i = 0; $i < 20; $i++) {
            $responses[] = $this->getJson('/api/products');
        }

        $finalMemory = memory_get_usage(true);
        $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024;

        $this->assertLessThan(60, $memoryUsed); // Should use less than 60MB
    }

    #[Test]
    public function memory_usage_is_consistent_across_multiple_runs()
    {
        $memoryUsages = [];

        for ($run = 0; $run < 5; $run++) {
            $initialMemory = memory_get_usage(true);

            // Perform same operations
            $products = \App\Models\Product::factory()->count(100)->create();

            $finalMemory = memory_get_usage(true);
            $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024;

            $memoryUsages[] = $memoryUsed;
        }

        // Check that memory usage is consistent
        $averageMemory = array_sum($memoryUsages) / count($memoryUsages);
        $maxDeviation = max($memoryUsages) - min($memoryUsages);

        $this->assertLessThan(10, $maxDeviation); // Deviation should be less than 10MB
    }

    #[Test]
    public function memory_usage_scales_linearly_with_data_size()
    {
        $dataSizes = [100, 200, 500, 1000];
        $memoryUsages = [];

        foreach ($dataSizes as $size) {
            $initialMemory = memory_get_usage(true);

            $products = \App\Models\Product::factory()->count($size)->create();

            $finalMemory = memory_get_usage(true);
            $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024;

            $memoryUsages[] = $memoryUsed;
        }

        // Check that memory usage scales linearly
        $firstUsage = $memoryUsages[0];
        $lastUsage = $memoryUsages[count($memoryUsages) - 1];
        $expectedRatio = $dataSizes[count($dataSizes) - 1] / $dataSizes[0];
        $actualRatio = $lastUsage / $firstUsage;

        $this->assertLessThan(2, abs($expectedRatio - $actualRatio)); // Should be roughly linear
    }
}
