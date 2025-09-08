<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_prevents_sql_injection_in_search()
    {
        $response = $this->getJson('/api/price-search?q=test\'; DROP TABLE products; --');
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }

    /**
     * @test
     */
    public function it_prevents_xss_attacks()
    {
        $response = $this->getJson('/api/price-search?q=<script>alert("xss")</script>');
        
        $response->assertStatus(200);
        $response->assertDontSee('<script>');
    }

    /**
     * @test
     */
    public function it_requires_authentication_for_protected_routes()
    {
        $response = $this->getJson('/wishlist');
        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function it_prevents_csrf_attacks()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/wishlist', [
            'product_id' => 1,
            '_token' => 'invalid_token'
        ]);

        $response->assertStatus(419);
    }

    /**
     * @test
     */
    public function it_validates_input_sanitization()
    {
        $response = $this->getJson('/api/price-search?q=test%20%3Cscript%3Ealert%281%29%3C%2Fscript%3E');
        
        $response->assertStatus(200);
        $response->assertDontSee('<script>');
    }

    /**
     * @test
     */
    public function it_prevents_mass_assignment()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/wishlist', [
            'product_id' => 1,
            'is_admin' => true,
            'created_at' => '2023-01-01'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('wishlists', [
            'is_admin' => true
        ]);
    }

    /**
     * @test
     */
    public function it_handles_rate_limiting()
    {
        // Make multiple requests quickly
        for ($i = 0; $i < 100; $i++) {
            $response = $this->getJson('/api/price-search?q=test');
            if ($response->status() === 429) {
                break;
            }
        }

        $response->assertStatus(429);
    }

    /**
     * @test
     */
    public function it_prevents_directory_traversal()
    {
        $response = $this->getJson('/api/price-search?q=../../../etc/passwd');
        
        $response->assertStatus(200);
        $response->assertDontSee('root:');
    }

    /**
     * @test
     */
    public function it_validates_file_upload_security()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = \Illuminate\Http\UploadedFile::fake()->create('test.php', 100);
        
        $response = $this->postJson('/api/upload', [
            'file' => $file
        ]);

        $response->assertStatus(422);
    }

    /**
     * @test
     */
    public function it_prevents_unauthorized_access_to_admin_routes()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $response = $this->getJson('/admin/dashboard');
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function it_validates_session_security()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test session regeneration
        $response = $this->postJson('/logout');
        $response->assertStatus(200);

        // Verify user is logged out
        $this->assertGuest();
    }
}
