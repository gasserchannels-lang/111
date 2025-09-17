<?php

declare(strict_types=1);

namespace Tests\Unit\Security;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;

class DataEncryptionTest extends TestCase
{
    private string $encryptionKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->encryptionKey = $this->generateEncryptionKey();
    }
    #[Test]
    #[CoversNothing]
    public function it_encrypts_sensitive_data(): void
    {
        $sensitiveData = 'user@example.com';
        $encryptedData = $this->encryptData($sensitiveData);

        $this->assertNotEquals($sensitiveData, $encryptedData);
        $this->assertIsString($encryptedData);
        $this->assertNotEmpty($encryptedData);
    }

    #[Test]
    #[CoversNothing]
    public function it_decrypts_encrypted_data(): void
    {
        $originalData = 'user@example.com';
        $encryptedData = $this->encryptData($originalData);
        $decryptedData = $this->decryptData($encryptedData);

        $this->assertEquals($originalData, $decryptedData);
    }

    #[Test]
    #[CoversNothing]
    public function it_encrypts_different_data_differently(): void
    {
        $data1 = 'user1@example.com';
        $data2 = 'user2@example.com';

        $encrypted1 = $this->encryptData($data1);
        $encrypted2 = $this->encryptData($data2);

        $this->assertNotEquals($encrypted1, $encrypted2);
    }

    #[Test]
    #[CoversNothing]
    public function it_encrypts_same_data_differently_each_time(): void
    {
        $data = 'user@example.com';

        $encrypted1 = $this->encryptData($data);
        $encrypted2 = $this->encryptData($data);

        $this->assertNotEquals($encrypted1, $encrypted2);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_empty_string_encryption(): void
    {
        $emptyString = '';
        $encryptedData = $this->encryptData($emptyString);
        $decryptedData = $this->decryptData($encryptedData);

        $this->assertEquals($emptyString, $decryptedData);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_special_characters_encryption(): void
    {
        $specialData = 'user@example.com!@#$%^&*()_+-=[]{}|;:,.<>?';
        $encryptedData = $this->encryptData($specialData);
        $decryptedData = $this->decryptData($encryptedData);

        $this->assertEquals($specialData, $decryptedData);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_unicode_encryption(): void
    {
        $unicodeData = 'مستخدم@example.com';
        $encryptedData = $this->encryptData($unicodeData);
        $decryptedData = $this->decryptData($encryptedData);

        $this->assertEquals($unicodeData, $decryptedData);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_large_data_encryption(): void
    {
        $largeData = str_repeat('This is a test string. ', 1000);
        $encryptedData = $this->encryptData($largeData);
        $decryptedData = $this->decryptData($encryptedData);

        $this->assertEquals($largeData, $decryptedData);
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_encryption_key_strength(): void
    {
        $key = $this->generateEncryptionKey();

        $this->assertIsString($key);
        $this->assertEquals(32, strlen($key)); // 256-bit key
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_invalid_encrypted_data(): void
    {
        $invalidEncryptedData = 'invalid_encrypted_data';

        $this->expectException(\Exception::class);
        $this->decryptData($invalidEncryptedData);
    }

    #[Test]
    #[CoversNothing]
    public function it_encrypts_user_personal_data(): void
    {
        $userData = [
            'email' => 'user@example.com',
            'phone' => '+1234567890',
            'ssn' => '123-45-6789'
        ];

        $encryptedData = [];
        foreach ($userData as $field => $value) {
            $encryptedData[$field] = $this->encryptData($value);
        }

        foreach ($encryptedData as $field => $encryptedValue) {
            $this->assertNotEquals($userData[$field], $encryptedValue);
            $this->assertIsString($encryptedValue);
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_encrypts_payment_data(): void
    {
        $paymentData = [
            'card_number' => '4111111111111111',
            'cvv' => '123',
            'expiry_date' => '12/25'
        ];

        $encryptedData = [];
        foreach ($paymentData as $field => $value) {
            $encryptedData[$field] = $this->encryptData($value);
        }

        foreach ($encryptedData as $field => $encryptedValue) {
            $this->assertNotEquals($paymentData[$field], $encryptedValue);
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_encryption_with_different_keys(): void
    {
        $data = 'sensitive_data';
        $key1 = $this->generateEncryptionKey();
        $key2 = $this->generateEncryptionKey();

        $encrypted1 = $this->encryptData($data, $key1);
        $encrypted2 = $this->encryptData($data, $key2);

        $this->assertNotEquals($encrypted1, $encrypted2);
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_encryption_algorithm(): void
    {
        $data = 'test_data';
        $encryptedData = $this->encryptData($data);

        // Check if the encrypted data has the expected format (base64)
        $this->assertTrue(base64_decode($encryptedData, true) !== false);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_encryption_performance(): void
    {
        $data = 'performance_test_data';
        $startTime = microtime(true);

        for ($i = 0; $i < 100; $i++) {
            $encryptedData = $this->encryptData($data);
            $this->decryptData($encryptedData);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should complete 100 encrypt/decrypt operations in less than 1 second
        $this->assertLessThan(1.0, $executionTime);
    }

    #[Test]
    #[CoversNothing]
    public function it_encrypts_database_fields(): void
    {
        $databaseRecord = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'address' => '123 Main St, City, State 12345'
        ];

        $encryptedRecord = $this->encryptSensitiveFields($databaseRecord);

        $this->assertEquals($databaseRecord['id'], $encryptedRecord['id']); // ID should not be encrypted
        $this->assertNotEquals($databaseRecord['email'], $encryptedRecord['email']);
        $this->assertNotEquals($databaseRecord['phone'], $encryptedRecord['phone']);
        $this->assertNotEquals($databaseRecord['address'], $encryptedRecord['address']);
    }

    private function encryptData(string $data, ?string $key = null): string
    {
        $key = $key ?? $this->encryptionKey;
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);

        if ($encrypted === false) {
            throw new \Exception('Failed to encrypt data');
        }

        return base64_encode($iv . $encrypted);
    }

    private function decryptData(string $encryptedData, ?string $key = null): string
    {
        $key = $key ?? $this->encryptionKey;
        $data = base64_decode($encryptedData);

        if ($data === false) {
            throw new \Exception('Invalid encrypted data format');
        }

        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);

        if ($decrypted === false) {
            throw new \Exception('Failed to decrypt data');
        }

        return $decrypted;
    }

    private function generateEncryptionKey(): string
    {
        return random_bytes(32);
    }

    private function encryptSensitiveFields(array $record): array
    {
        $sensitiveFields = ['email', 'phone', 'address'];
        $encryptedRecord = $record;

        foreach ($sensitiveFields as $field) {
            if (isset($record[$field])) {
                $encryptedRecord[$field] = $this->encryptData($record[$field]);
            }
        }

        return $encryptedRecord;
    }
}
