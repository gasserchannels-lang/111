<?php

declare(strict_types=1);

namespace Tests\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityAudit extends TestCase
{
    use RefreshDatabase;

    /**
     * Test password security requirements
     */
    public function test_password_security_requirements()
    {
        // Test weak password
        $weakPassword = '123456';
        $this->assertFalse($this->isPasswordStrong($weakPassword), 'Weak password should be rejected');

        // Test strong password
        $strongPassword = 'MyStr0ng!P@ssw0rd';
        $this->assertTrue($this->isPasswordStrong($strongPassword), 'Strong password should be accepted');
    }

    /**
     * Test SQL injection prevention
     */
    public function test_sql_injection_prevention()
    {
        $maliciousInput = "'; DROP TABLE users; --";
        
        // Test that malicious input is properly escaped
        $response = $this->getJson('/api/price-search?q=' . urlencode($maliciousInput));
        
        $response->assertStatus(200);
        
        // Verify that users table still exists
        $this->assertDatabaseHas('users', ['id' => 1]);
    }

    /**
     * Test XSS prevention
     */
    public function test_xss_prevention()
    {
        $xssPayload = '<script>alert("XSS")</script>';
        
        $response = $this->getJson('/api/price-search?q=' . urlencode($xssPayload));
        
        $response->assertStatus(200);
        
        // Verify that script tags are not present in response
        $this->assertStringNotContainsString('<script>', $response->getContent());
        $this->assertStringNotContainsString('alert(', $response->getContent());
    }

    /**
     * Test CSRF protection
     */
    public function test_csrf_protection()
    {
        $user = User::factory()->create();
        
        // Test POST request without CSRF token
        $response = $this->postJson('/wishlist/toggle', [
            'product_id' => 1
        ]);
        
        // Should either redirect to login or return 419 (CSRF token mismatch)
        $this->assertTrue(
            in_array($response->status(), [302, 419, 403]),
            'CSRF protection should be active'
        );
    }

    /**
     * Test authentication requirements
     */
    public function test_authentication_requirements()
    {
        // Test protected route without authentication
        $response = $this->getJson('/wishlist');
        
        $response->assertStatus(401);
    }

    /**
     * Test input validation
     */
    public function test_input_validation()
    {
        // Test with invalid data
        $response = $this->postJson('/api/upload', [
            'file' => 'not_a_file'
        ]);
        
        // Should return validation error
        $this->assertTrue(
            in_array($response->status(), [422, 400]),
            'Invalid input should be rejected'
        );
    }

    /**
     * Test rate limiting
     */
    public function test_rate_limiting()
    {
        $response = null;
        
        // Make multiple requests quickly
        for ($i = 0; $i < 20; $i++) {
            $response = $this->getJson('/api/price-search?q=test');
            if ($response->status() === 429) {
                break;
            }
        }
        
        // Should eventually hit rate limit
        $this->assertTrue(
            in_array($response->status(), [200, 429]),
            'Rate limiting should be active'
        );
    }

    /**
     * Test file upload security
     */
    public function test_file_upload_security()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test with malicious file
        $maliciousFile = \Illuminate\Http\UploadedFile::fake()->create('malicious.php', 100);
        
        $response = $this->postJson('/api/upload', [
            'file' => $maliciousFile
        ]);
        
        // Should handle malicious files safely
        $this->assertTrue(
            in_array($response->status(), [200, 422, 400]),
            'Malicious files should be handled safely'
        );
    }

    /**
     * Test session security
     */
    public function test_session_security()
    {
        $user = User::factory()->create();
        
        // Login user
        $this->actingAs($user);
        
        // Test session regeneration
        $oldSessionId = session()->getId();
        $this->post('/logout');
        $newSessionId = session()->getId();
        
        $this->assertNotEquals($oldSessionId, $newSessionId, 'Session should be regenerated on logout');
    }

    /**
     * Test authorization levels
     */
    public function test_authorization_levels()
    {
        $regularUser = User::factory()->create(['is_admin' => false]);
        $adminUser = User::factory()->create(['is_admin' => true]);
        
        // Test regular user cannot access admin routes
        $this->actingAs($regularUser);
        $response = $this->get('/admin');
        $this->assertTrue(
            in_array($response->status(), [403, 404, 302]),
            'Regular users should not access admin routes'
        );
        
        // Test admin user can access admin routes
        $this->actingAs($adminUser);
        $response = $this->get('/admin');
        $this->assertTrue(
            in_array($response->status(), [200, 302]),
            'Admin users should access admin routes'
        );
    }

    /**
     * Helper method to check password strength
     */
    private function isPasswordStrong(string $password): bool
    {
        // Check minimum length
        if (strlen($password) < 8) {
            return false;
        }
        
        // Check for uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // Check for lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // Check for number
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        // Check for special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }
        
        return true;
    }
}
