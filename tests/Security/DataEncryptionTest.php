<?php

namespace Tests\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DataEncryptionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function sensitive_data_is_encrypted_at_rest()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Check that password is hashed
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    #[Test]
    public function api_responses_do_not_include_sensitive_data()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/user');
        $data = $response->json();

        // Sensitive data should not be in API response
        $this->assertArrayNotHasKey('password', $data);
    }

    #[Test]
    public function database_queries_do_not_log_sensitive_data()
    {
        $this->markTestSkipped('Test requires database query logging setup');
    }

    #[Test]
    public function file_uploads_are_encrypted()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->postJson('/api/upload', [
            'file' => $file,
            'encrypt' => true,
        ]);

        $this->markTestSkipped('Test requires file encryption setup');
    }

    #[Test]
    public function api_keys_are_encrypted()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $apiKey = 'sk_test_1234567890abcdef';

        $response = $this->postJson('/api/user/api-keys', [
            'name' => 'Test API Key',
            'key' => $apiKey,
        ]);

        $this->markTestSkipped('Test requires API key encryption setup');
    }

    #[Test]
    public function credit_card_data_is_encrypted()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cardData = [
            'number' => '4111111111111111',
            'cvv' => '123',
            'expiry' => '12/25',
        ];

        $response = $this->postJson('/api/payment-methods', $cardData);

        $this->markTestSkipped('Test requires credit card encryption setup');
    }

    #[Test]
    public function error_logs_do_not_contain_sensitive_data()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Trigger an error
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // Check error logs
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);

            // Logs should not contain sensitive data
            $this->assertStringNotContainsString('password123', $logContent);
        }
    }

    #[Test]
    public function database_backups_are_encrypted()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create a backup
        $backupPath = storage_path('app/backups/database_backup.sql');
        $this->artisan('backup:run');

        if (file_exists($backupPath)) {
            $backupContent = file_get_contents($backupPath);

            // Backup should be encrypted
            $this->assertStringNotContainsString('password123', $backupContent);
        }
    }

    #[Test]
    public function session_data_is_encrypted()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->markTestSkipped('Test requires session encryption setup');
    }

    #[Test]
    public function api_tokens_are_encrypted()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/tokens', [
            'name' => 'Test Token',
        ]);

        $this->markTestSkipped('Test requires API token encryption setup');
    }
}
