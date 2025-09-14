<?php

namespace Tests\Performance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoadTimeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function homepage_loads_within_acceptable_time()
    {
        $startTime = microtime(true);

        $response = $this->get('/');

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->assertEquals(200, $response->status());
        $this->assertLessThan(2000, $loadTime); // Should load within 2 seconds
    }

    #[Test]
    public function api_endpoints_load_within_acceptable_time()
    {
        $apiEndpoints = [
            '/api/health',
            '/api/products',
            '/api/categories',
            '/api/brands',
        ];

        foreach ($apiEndpoints as $endpoint) {
            $startTime = microtime(true);

            $response = $this->get($endpoint);

            $endTime = microtime(true);
            $loadTime = ($endTime - $startTime) * 1000;

            $this->assertLessThan(1000, $loadTime); // Should load within 1 second
        }
    }

    #[Test]
    public function product_list_page_loads_within_acceptable_time()
    {
        $startTime = microtime(true);

        $response = $this->get('/products');

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        $this->assertEquals(200, $response->status());
        $this->assertLessThan(3000, $loadTime); // Should load within 3 seconds
    }

    #[Test]
    public function search_function_performs_within_acceptable_time()
    {
        $searchQueries = [
            'laptop',
            'phone',
            'clothing',
            'electronics',
        ];

        foreach ($searchQueries as $query) {
            $startTime = microtime(true);

            $response = $this->getJson('/api/search?q='.urlencode($query));

            $endTime = microtime(true);
            $loadTime = ($endTime - $startTime) * 1000;

            $this->assertLessThan(2000, $loadTime); // Should search within 2 seconds
        }
    }

    #[Test]
    public function user_registration_performs_within_acceptable_time()
    {
        $startTime = microtime(true);

        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(3000, $loadTime); // Should register within 3 seconds
    }

    #[Test]
    public function user_login_performs_within_acceptable_time()
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $startTime = microtime(true);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(2000, $loadTime); // Should login within 2 seconds
    }

    #[Test]
    public function database_queries_perform_within_acceptable_time()
    {
        $startTime = microtime(true);

        // Perform complex database operations
        $products = \App\Models\Product::with(['category', 'brand'])
            ->where('is_active', true)
            ->where('price', '>', 100)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(1000, $loadTime); // Should query within 1 second
    }

    #[Test]
    public function file_upload_performs_within_acceptable_time()
    {
        // Skip this test if GD extension is not available
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension not available');
        }

        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $file = \Illuminate\Http\UploadedFile::fake()->image('test.jpg', 100, 100);

        $startTime = microtime(true);

        $response = $this->postJson('/api/upload', [
            'file' => $file,
        ]);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(5000, $loadTime); // Should upload within 5 seconds
    }

    #[Test]
    public function admin_panel_loads_within_acceptable_time()
    {
        $admin = \App\Models\User::factory()->create([
            'is_admin' => true,
        ]);
        $this->actingAs($admin);

        $startTime = microtime(true);

        $response = $this->get('/admin/dashboard');

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        $this->assertEquals(200, $response->status());
        $this->assertLessThan(3000, $loadTime); // Should load within 3 seconds
    }

    #[Test]
    public function concurrent_requests_handle_gracefully()
    {
        $startTime = microtime(true);

        // Simulate concurrent requests
        $responses = [];
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->get('/api/health');
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(10000, $totalTime); // Should handle 10 requests within 10 seconds

        foreach ($responses as $response) {
            $this->assertNotEquals(500, $response->status());
        }
    }
}
