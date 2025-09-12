<?php

namespace Tests\Performance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiResponseTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function api_endpoints_respond_within_acceptable_time()
    {
        $apiEndpoints = [
            ['method' => 'GET', 'url' => '/api/health'],
            ['method' => 'GET', 'url' => '/api/products'],
            ['method' => 'GET', 'url' => '/api/categories'],
            ['method' => 'GET', 'url' => '/api/brands'],
        ];

        foreach ($apiEndpoints as $endpoint) {
            $startTime = microtime(true);

            $response = $this->call($endpoint['method'], $endpoint['url']);

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $this->assertLessThan(1000, $responseTime); // Should respond within 1 second
        }
    }

    /** @test */
    public function authenticated_api_endpoints_respond_within_acceptable_time()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $authenticatedEndpoints = [
            ['method' => 'GET', 'url' => '/api/user'],
            ['method' => 'GET', 'url' => '/api/orders'],
            ['method' => 'GET', 'url' => '/api/wishlist'],
            ['method' => 'GET', 'url' => '/api/profile'],
        ];

        foreach ($authenticatedEndpoints as $endpoint) {
            $startTime = microtime(true);

            $response = $this->call($endpoint['method'], $endpoint['url']);

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $this->assertLessThan(1500, $responseTime); // Should respond within 1.5 seconds
        }
    }

    /** @test */
    public function api_post_requests_respond_within_acceptable_time()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $postEndpoints = [
            ['url' => '/api/products', 'data' => ['name' => 'Test Product', 'price' => 100]],
            ['url' => '/api/orders', 'data' => ['product_id' => 1, 'quantity' => 2]],
            ['url' => '/api/wishlist', 'data' => ['product_id' => 1]],
        ];

        foreach ($postEndpoints as $endpoint) {
            $startTime = microtime(true);

            $response = $this->postJson($endpoint['url'], $endpoint['data']);

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $this->assertLessThan(2000, $responseTime); // Should respond within 2 seconds
        }
    }

    /** @test */
    public function api_put_requests_respond_within_acceptable_time()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $putEndpoints = [
            ['url' => '/api/products/1', 'data' => ['name' => 'Updated Product']],
            ['url' => '/api/user/profile', 'data' => ['name' => 'Updated Name']],
            ['url' => '/api/orders/1', 'data' => ['status' => 'shipped']],
        ];

        foreach ($putEndpoints as $endpoint) {
            $startTime = microtime(true);

            $response = $this->putJson($endpoint['url'], $endpoint['data']);

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $this->assertLessThan(2000, $responseTime); // Should respond within 2 seconds
        }
    }

    /** @test */
    public function api_delete_requests_respond_within_acceptable_time()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $deleteEndpoints = [
            '/api/products/1',
            '/api/orders/1',
            '/api/wishlist/1',
        ];

        foreach ($deleteEndpoints as $endpoint) {
            $startTime = microtime(true);

            $response = $this->deleteJson($endpoint);

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $this->assertLessThan(1500, $responseTime); // Should respond within 1.5 seconds
        }
    }

    /** @test */
    public function api_search_responds_within_acceptable_time()
    {
        $searchQueries = [
            'laptop',
            'phone',
            'clothing',
            'electronics',
            'samsung',
        ];

        foreach ($searchQueries as $query) {
            $startTime = microtime(true);

            $response = $this->getJson('/api/search?q='.urlencode($query));

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $this->assertLessThan(2000, $responseTime); // Should respond within 2 seconds
        }
    }

    /** @test */
    public function api_pagination_responds_within_acceptable_time()
    {
        $paginationEndpoints = [
            '/api/products?page=1&per_page=20',
            '/api/products?page=2&per_page=20',
            '/api/products?page=5&per_page=50',
        ];

        foreach ($paginationEndpoints as $endpoint) {
            $startTime = microtime(true);

            $response = $this->getJson($endpoint);

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $this->assertLessThan(1500, $responseTime); // Should respond within 1.5 seconds
        }
    }

    /** @test */
    public function api_filtering_responds_within_acceptable_time()
    {
        $filterEndpoints = [
            '/api/products?category=electronics',
            '/api/products?brand=samsung',
            '/api/products?price_min=100&price_max=500',
            '/api/products?rating_min=4',
        ];

        foreach ($filterEndpoints as $endpoint) {
            $startTime = microtime(true);

            $response = $this->getJson($endpoint);

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $this->assertLessThan(2000, $responseTime); // Should respond within 2 seconds
        }
    }

    /** @test */
    public function api_concurrent_requests_handle_gracefully()
    {
        $startTime = microtime(true);

        // Simulate concurrent API requests
        $responses = [];
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->getJson('/api/products');
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(5000, $totalTime); // Should handle 10 requests within 5 seconds

        foreach ($responses as $response) {
            $this->assertNotEquals(500, $response->status());
        }
    }

    /** @test */
    public function api_response_times_are_consistent()
    {
        $responseTimes = [];

        // Make multiple requests to the same endpoint
        for ($i = 0; $i < 10; $i++) {
            $startTime = microtime(true);

            $response = $this->getJson('/api/products');

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $responseTimes[] = $responseTime;
        }

        // Check that response times are consistent
        $averageTime = array_sum($responseTimes) / count($responseTimes);
        $maxDeviation = max($responseTimes) - min($responseTimes);

        $this->assertLessThan(500, $maxDeviation); // Deviation should be less than 500ms
        $this->assertLessThan(1000, $averageTime); // Average should be less than 1 second
    }

    /** @test */
    public function api_error_responses_are_fast()
    {
        $errorEndpoints = [
            '/api/nonexistent',
            '/api/products/999999',
            '/api/invalid-endpoint',
        ];

        foreach ($errorEndpoints as $endpoint) {
            $startTime = microtime(true);

            $response = $this->getJson($endpoint);

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $this->assertLessThan(500, $responseTime); // Error responses should be fast
        }
    }
}
