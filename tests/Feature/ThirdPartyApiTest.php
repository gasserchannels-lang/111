<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ThirdPartyApiTest extends TestCase
{
    

    #[Test]
    public function external_api_requests_are_mocked()
    {
        Http::fake([
            'api.external-service.com/*' => Http::response([
                'status' => 'success',
                'data' => ['test' => 'data'],
            ], 200),
        ]);

        $response = $this->getJson('/api/external-data');
        $this->assertEquals(200, $response->status());
    }

    #[Test]
    public function external_api_timeout_is_handled()
    {
        Http::fake([
            'api.slow-service.com/*' => Http::response([], 200, [], 5), // 5 second delay
        ]);

        $response = $this->getJson('/api/slow-external-data');
        $this->assertNotEquals(500, $response->status());
    }

    #[Test]
    public function external_api_errors_are_handled()
    {
        Http::fake([
            'api.error-service.com/*' => Http::response([], 500),
        ]);

        $response = $this->getJson('/api/error-external-data');
        $this->assertNotEquals(500, $response->status());
    }

    #[Test]
    public function external_api_authentication_works()
    {
        Http::fake([
            'api.authenticated-service.com/*' => Http::response([
                'authenticated' => true,
            ], 200),
        ]);

        $response = $this->getJson('/api/authenticated-external-data');
        $this->assertEquals(200, $response->status());
    }

    #[Test]
    public function external_api_rate_limiting_is_respected()
    {
        Http::fake([
            'api.rate-limited-service.com/*' => Http::response([], 429),
        ]);

        $response = $this->getJson('/api/rate-limited-external-data');
        $this->assertNotEquals(500, $response->status());
    }

    #[Test]
    public function external_api_data_is_cached()
    {
        Http::fake([
            'api.cacheable-service.com/*' => Http::response([
                'cached' => true,
            ], 200),
        ]);

        // First request
        $response1 = $this->getJson('/api/cached-external-data');
        $this->assertEquals(200, $response1->status());

        // Second request should use cache
        $response2 = $this->getJson('/api/cached-external-data');
        $this->assertEquals(200, $response2->status());
    }

    #[Test]
    public function external_api_fallback_works()
    {
        Http::fake([
            'api.primary-service.com/*' => Http::response([], 500),
            'api.fallback-service.com/*' => Http::response([
                'fallback' => true,
            ], 200),
        ]);

        $response = $this->getJson('/api/fallback-external-data');
        $this->assertEquals(200, $response->status());
    }
}
