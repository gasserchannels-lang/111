<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function api_returns_json_response()
    {
        $response = $this->getJson('/api/health');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');
    }

    /** @test */
    public function api_handles_get_requests()
    {
        $response = $this->getJson('/api/products');
        $response->assertStatus(200);
    }

    /** @test */
    public function api_handles_post_requests()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/products', [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100.00,
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function api_handles_put_requests()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->putJson('/api/products/1', [
            'name' => 'Updated Product',
            'price' => 150.00,
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function api_handles_delete_requests()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->deleteJson('/api/products/1');
        $response->assertStatus(200);
    }

    /** @test */
    public function api_returns_proper_error_codes()
    {
        $response = $this->getJson('/api/nonexistent');
        $response->assertStatus(404);
    }

    /** @test */
    public function api_validates_required_fields()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/products', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'price']);
    }
}
