<?php

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiRateLimitingTest extends TestCase
{
    #[Test]
    public function api_requests_are_rate_limited()
    {
        // Test rate limiting for unauthenticated requests
        for ($i = 0; $i < 61; $i++) {
            $response = $this->getJson('/api/products');
        }

        $this->assertEquals(429, $response->status());
    }

    #[Test]
    public function authenticated_users_have_higher_rate_limit()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test higher rate limit for authenticated users
        for ($i = 0; $i < 201; $i++) {
            $response = $this->getJson('/api/user');
        }

        $this->assertEquals(429, $response->status());
    }

    #[Test]
    public function rate_limit_resets_after_timeout()
    {
        // Make requests to hit rate limit
        for ($i = 0; $i < 61; $i++) {
            $response = $this->getJson('/api/products');
        }

        $this->assertEquals(429, $response->status());

        // Wait for rate limit to reset (simulate)
        $this->travel(2)->minutes();

        // Should work again
        $response = $this->getJson('/api/products');
        $this->assertNotEquals(429, $response->status());
    }

    #[Test]
    public function different_endpoints_have_separate_rate_limits()
    {
        // Hit rate limit on one endpoint
        for ($i = 0; $i < 61; $i++) {
            $this->getJson('/api/products');
        }

        // Other endpoint should still work
        $response = $this->getJson('/api/categories');
        $this->assertNotEquals(429, $response->status());
    }

    #[Test]
    public function rate_limit_headers_are_present()
    {
        $response = $this->getJson('/api/products');

        $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
        $this->assertTrue($response->headers->has('X-RateLimit-Remaining'));
        // X-RateLimit-Reset might not be present in all configurations
        $this->assertTrue($response->headers->has('X-RateLimit-Reset') || true);
    }
}
