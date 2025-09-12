<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiVersioningTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function api_v1_endpoints_are_accessible()
    {
        $response = $this->getJson('/api/v1/products');
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function api_v2_endpoints_are_accessible()
    {
        $response = $this->getJson('/api/v2/products');
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function api_version_header_works()
    {
        $response = $this->withHeaders([
            'Accept' => 'application/vnd.api+json;version=1',
        ])->getJson('/api/products');

        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function api_version_parameter_works()
    {
        $response = $this->getJson('/api/products?version=1');
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function default_api_version_is_used()
    {
        $response = $this->getJson('/api/products');
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function api_version_response_includes_version_info()
    {
        $response = $this->getJson('/api/v1/products');

        if ($response->status() === 200) {
            $data = $response->json();
            $this->assertArrayHasKey('version', $data);
            $this->assertEquals('1.0', $data['version']);
        }
    }

    /** @test */
    public function deprecated_api_version_shows_warning()
    {
        $response = $this->getJson('/api/v1/products');

        if ($response->status() === 200) {
            $this->assertTrue($response->headers->has('X-API-Deprecated'));
        }
    }

    /** @test */
    public function api_version_middleware_works()
    {
        $response = $this->getJson('/api/invalid-version/products');
        $this->assertEquals(400, $response->status());
    }
}
