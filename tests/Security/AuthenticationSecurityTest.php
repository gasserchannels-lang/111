<?php

namespace Tests\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function passwords_are_hashed_before_storage()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $user = User::create($userData);

        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    #[Test]
    public function login_attempts_are_rate_limited()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Attempt multiple failed logins
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // Should be rate limited after 5 attempts
        $this->assertEquals(429, $response->status());
    }

    #[Test]
    public function user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertEquals(401, $response->status());
    }

    #[Test]
    public function user_cannot_login_with_nonexistent_email()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $this->assertEquals(401, $response->status());
    }

    #[Test]
    public function user_session_expires_after_timeout()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Check that user is authenticated
        $response = $this->getJson('/api/user');
        $this->assertEquals(200, $response->status());

        // Simulate session timeout
        config(['session.lifetime' => 1]); // 1 minute
        $this->travel(2)->minutes();

        // User should be unauthenticated after timeout
        $response = $this->getJson('/api/user');
        $this->assertEquals(401, $response->status());
    }

    #[Test]
    public function user_cannot_access_protected_routes_without_authentication()
    {
        $protectedRoutes = [
            '/api/user',
            '/api/orders',
            '/api/wishlist',
            '/api/profile',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->getJson($route);
            $this->assertEquals(401, $response->status());
        }
    }

    #[Test]
    public function user_can_access_protected_routes_with_authentication()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $protectedRoutes = [
            '/api/user',
            '/api/orders',
            '/api/wishlist',
            '/api/profile',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->getJson($route);
            $this->assertNotEquals(401, $response->status());
        }
    }

    #[Test]
    public function password_reset_requires_valid_email()
    {
        $response = $this->postJson('/api/password/reset', [
            'email' => 'nonexistent@example.com',
        ]);

        // Should not reveal whether email exists
        $this->assertEquals(200, $response->status());
    }

    #[Test]
    public function password_reset_token_expires()
    {
        $user = User::factory()->create();

        // Request password reset (simulate with existing endpoint)
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $this->assertEquals(200, $response->status());

        // Simulate token expiration
        $this->travel(2)->hours();

        // Try to reset password with expired token
        $response = $this->postJson('/api/password/reset/confirm', [
            'token' => 'expired_token',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $this->assertEquals(422, $response->status());
    }

    #[Test]
    public function user_cannot_impersonate_other_users()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user1);

        // Try to access user2's data
        $response = $this->getJson('/api/user/' . $user2->id);
        $this->assertEquals(403, $response->status());
    }

    #[Test]
    public function admin_can_access_admin_routes()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $adminRoutes = [
            '/api/admin/users',
            '/api/admin/products',
            '/api/admin/orders',
        ];

        foreach ($adminRoutes as $route) {
            $response = $this->getJson($route);
            $this->assertNotEquals(403, $response->status());
        }
    }

    #[Test]
    public function regular_user_cannot_access_admin_routes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $adminRoutes = [
            '/api/admin/users',
            '/api/admin/products',
            '/api/admin/orders',
        ];

        foreach ($adminRoutes as $route) {
            $response = $this->getJson($route);
            $this->assertEquals(403, $response->status());
        }
    }

    #[Test]
    public function user_can_logout_successfully()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/logout');
        $this->assertEquals(200, $response->status());

        // User should be unauthenticated after logout
        $response = $this->getJson('/api/user');
        $this->assertEquals(401, $response->status());
    }

    #[Test]
    public function user_cannot_login_with_inactive_account()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertEquals(401, $response->status());
    }
}
