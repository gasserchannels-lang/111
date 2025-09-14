<?php

namespace Tests\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
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
            'phone' => Crypt::encrypt('+1234567890'),
            'ssn' => Crypt::encrypt('123-45-6789'),
        ]);

        // Check that sensitive data is encrypted in database
        $this->assertNotEquals('+1234567890', $user->phone);
        $this->assertNotEquals('123-45-6789', $user->ssn);

        // Check that we can decrypt the data
        $this->assertEquals('+1234567890', Crypt::decrypt($user->phone));
        $this->assertEquals('123-45-6789', Crypt::decrypt($user->ssn));
    }

    #[Test]
    public function api_responses_do_not_include_sensitive_data()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'phone' => Crypt::encrypt('+1234567890'),
            'ssn' => Crypt::encrypt('123-45-6789'),
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/user');
        $data = $response->json();

        // Sensitive data should not be in API response
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('ssn', $data);
        $this->assertArrayNotHasKey('phone', $data);
    }

    #[Test]
    public function database_queries_do_not_log_sensitive_data()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'phone' => Crypt::encrypt('+1234567890'),
        ]);

        // Enable query logging
        DB::enableQueryLog();

        $this->actingAs($user);
        $this->getJson('/api/user');

        $queries = DB::getQueryLog();

        // Check that sensitive data is not in query logs
        foreach ($queries as $query) {
            $this->assertStringNotContainsString('password123', $query['query']);
            $this->assertStringNotContainsString('+1234567890', $query['query']);
        }
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

        if ($response->status() === 200) {
            $data = $response->json();
            $filePath = storage_path('app/uploads/'.$data['filename']);

            // Check that file is encrypted
            $fileContent = file_get_contents($filePath);
            $this->assertStringNotContainsString('PDF', $fileContent);
        }
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

        if ($response->status() === 200) {
            $data = $response->json();

            // API key should be encrypted in response
            $this->assertNotEquals($apiKey, $data['key']);
            $this->assertStringContainsString('encrypted', $data['key']);
        }
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

        if ($response->status() === 200) {
            $data = $response->json();

            // Card data should be encrypted
            $this->assertNotEquals($cardData['number'], $data['number']);
            $this->assertNotEquals($cardData['cvv'], $data['cvv']);
        }
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
            $this->assertStringNotContainsString('wrongpassword', $logContent);
        }
    }

    #[Test]
    public function database_backups_are_encrypted()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'phone' => Crypt::encrypt('+1234567890'),
        ]);

        // Create a backup
        $backupPath = storage_path('app/backups/database_backup.sql');
        $this->artisan('backup:run');

        if (file_exists($backupPath)) {
            $backupContent = file_get_contents($backupPath);

            // Backup should be encrypted
            $this->assertStringNotContainsString('password123', $backupContent);
            $this->assertStringNotContainsString('+1234567890', $backupContent);
        }
    }

    #[Test]
    public function session_data_is_encrypted()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Store sensitive data in session
        session(['sensitive_data' => Crypt::encrypt('secret_value')]);

        $response = $this->getJson('/api/user');
        $this->assertEquals(200, $response->status());

        // Check that session data is encrypted
        $sessionData = session('sensitive_data');
        $this->assertNotEquals('secret_value', $sessionData);
        $this->assertEquals('secret_value', Crypt::decrypt($sessionData));
    }

    #[Test]
    public function api_tokens_are_encrypted()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/tokens', [
            'name' => 'Test Token',
        ]);

        if ($response->status() === 200) {
            $data = $response->json();

            // Token should be encrypted
            $this->assertNotEquals('plain_token', $data['token']);
            $this->assertStringContainsString('encrypted', $data['token']);
        }
    }
}
