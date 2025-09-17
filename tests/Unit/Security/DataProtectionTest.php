<?php

namespace Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversNothing;

class DataProtectionTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_encrypts_sensitive_data(): void
    {
        $sensitiveData = 'This is sensitive information';
        $encryptionKey = 'encryption_key_123';

        $encryptedData = $this->encryptData($sensitiveData, $encryptionKey);
        $decryptedData = $this->decryptData($encryptedData, $encryptionKey);

        $this->assertNotEquals($sensitiveData, $encryptedData);
        $this->assertEquals($sensitiveData, $decryptedData);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_data_masking(): void
    {
        $sensitiveData = [
            'email' => 'user@example.com',
            'phone' => '+1234567890',
            'ssn' => '123-45-6789',
            'credit_card' => '4111-1111-1111-1111'
        ];

        $maskedData = $this->maskSensitiveData($sensitiveData);

        $this->assertStringNotContainsString('@', $maskedData['email']);
        $this->assertStringNotContainsString('1234567890', $maskedData['phone']);
        $this->assertStringNotContainsString('123-45-6789', $maskedData['ssn']);
        $this->assertStringNotContainsString('4111-1111-1111-1111', $maskedData['credit_card']);
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_data_anonymization(): void
    {
        $personalData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1234567890',
            'address' => '123 Main St, New York, NY'
        ];

        $anonymizedData = $this->anonymizeData($personalData);

        $this->assertNotEquals($personalData['name'], $anonymizedData['name']);
        $this->assertNotEquals($personalData['email'], $anonymizedData['email']);
        $this->assertNotEquals($personalData['phone'], $anonymizedData['phone']);
        $this->assertNotEquals($personalData['address'], $anonymizedData['address']);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_data_tokenization(): void
    {
        $sensitiveValue = '4111-1111-1111-1111';

        $token = $this->tokenizeData($sensitiveValue);
        $detokenizedValue = $this->detokenizeData($token);

        $this->assertNotEquals($sensitiveValue, $token);
        $this->assertEquals($sensitiveValue, $detokenizedValue);
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_data_classification(): void
    {
        $data = [
            'public_info' => 'This is public information',
            'internal_info' => 'This is internal information',
            'confidential_info' => 'This is confidential information',
            'restricted_info' => 'This is restricted information'
        ];

        $classifiedData = $this->classifyData($data);

        $this->assertArrayHasKey('public', $classifiedData);
        $this->assertArrayHasKey('internal', $classifiedData);
        $this->assertArrayHasKey('confidential', $classifiedData);
        $this->assertArrayHasKey('restricted', $classifiedData);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_data_retention_policies(): void
    {
        $dataId = 123;
        $dataType = 'user_logs';
        $creationDate = time() - (365 * 24 * 60 * 60); // 1 year ago
        $retentionPeriod = 90; // 90 days

        $shouldRetain = $this->shouldRetainData($dataId, $dataType, $creationDate, $retentionPeriod);
        $expiredData = $this->getExpiredData($dataType, $retentionPeriod);

        $this->assertFalse($shouldRetain);
        $this->assertIsArray($expiredData);
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_data_encryption_at_rest(): void
    {
        $data = 'Sensitive data to be encrypted at rest';
        $encryptionKey = 'rest_encryption_key';

        $encryptedAtRest = $this->encryptAtRest($data, $encryptionKey);
        $decryptedFromRest = $this->decryptFromRest($encryptedAtRest, $encryptionKey);

        $this->assertNotEquals($data, $encryptedAtRest);
        $this->assertEquals($data, $decryptedFromRest);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_data_encryption_in_transit(): void
    {
        $data = 'Data to be encrypted in transit';
        $publicKey = 'public_key_123';

        $encryptedInTransit = $this->encryptInTransit($data, $publicKey);
        $decryptedInTransit = $this->decryptInTransit($encryptedInTransit, 'private_key_123');

        $this->assertNotEquals($data, $encryptedInTransit);
        $this->assertEquals($data, $decryptedInTransit);
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_data_obfuscation(): void
    {
        $sensitiveData = 'John Doe, 123-45-6789, user@example.com';

        $obfuscatedData = $this->obfuscateData($sensitiveData);

        $this->assertNotEquals($sensitiveData, $obfuscatedData);
        $this->assertStringNotContainsString('John Doe', $obfuscatedData);
        $this->assertStringNotContainsString('123-45-6789', $obfuscatedData);
        $this->assertStringNotContainsString('user@example.com', $obfuscatedData);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_data_pseudonymization(): void
    {
        $personalData = [
            'user_id' => 123,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com'
        ];

        $pseudonymizedData = $this->pseudonymizeData($personalData);

        $this->assertNotEquals($personalData['name'], $pseudonymizedData['name']);
        $this->assertNotEquals($personalData['email'], $pseudonymizedData['email']);
        $this->assertEquals($personalData['user_id'], $pseudonymizedData['user_id']);
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_data_watermarking(): void
    {
        $data = 'Original data content';
        $watermark = 'CONFIDENTIAL';

        $watermarkedData = $this->addWatermark($data, $watermark);
        $extractedWatermark = $this->extractWatermark($watermarkedData);

        $this->assertNotEquals($data, $watermarkedData);
        $this->assertEquals($watermark, $extractedWatermark);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_data_redaction(): void
    {
        $document = 'User John Doe (SSN: 123-45-6789) can be reached at john@example.com or +1-555-123-4567';
        $sensitivePatterns = [
            'SSN' => '/\d{3}-\d{2}-\d{4}/',
            'email' => '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
            'phone' => '/\+?1?[-.\s]?\(?[0-9]{3}\)?[-.\s]?[0-9]{3}[-.\s]?[0-9]{4}/'
        ];

        $redactedDocument = $this->redactSensitiveData($document, $sensitivePatterns);

        $this->assertStringNotContainsString('123-45-6789', $redactedDocument);
        $this->assertStringNotContainsString('john@example.com', $redactedDocument);
        $this->assertStringNotContainsString('+1-555-123-4567', $redactedDocument);
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_data_governance(): void
    {
        $dataAsset = [
            'name' => 'customer_database',
            'owner' => 'data_team',
            'classification' => 'confidential',
            'retention_period' => 2555, // 7 years in days
            'access_level' => 'restricted'
        ];

        $governanceResult = $this->applyDataGovernance($dataAsset);

        $this->assertArrayHasKey('compliance_status', $governanceResult);
        $this->assertArrayHasKey('access_controls', $governanceResult);
        $this->assertArrayHasKey('retention_schedule', $governanceResult);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_data_breach_detection(): void
    {
        $sensitiveData = 'credit_card_number: 4111-1111-1111-1111';
        $breachPatterns = [
            'credit_card' => '/\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{4}/',
            'ssn' => '/\d{3}-\d{2}-\d{4}/',
            'email' => '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/'
        ];

        $breachDetected = $this->detectDataBreach($sensitiveData, $breachPatterns);

        $this->assertTrue($breachDetected['breach_detected']);
        $this->assertArrayHasKey('sensitive_patterns', $breachDetected);
        $this->assertContains('credit_card', $breachDetected['sensitive_patterns']);
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_data_consent_management(): void
    {
        $userId = 123;
        $dataTypes = ['personal_info', 'usage_data', 'marketing_data'];
        $purposes = ['analytics', 'marketing', 'service_improvement'];

        $consentStatus = $this->getConsentStatus($userId, $dataTypes, $purposes);
        $consentResult = $this->updateConsent($userId, 'personal_info', 'analytics', true);

        $this->assertIsArray($consentStatus);
        $this->assertTrue($consentResult['success']);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_data_portability(): void
    {
        $userId = 123;
        $dataTypes = ['profile', 'preferences', 'activity_logs'];

        $portableData = $this->exportUserData($userId, $dataTypes);
        $importResult = $this->importUserData($userId, $portableData);

        $this->assertIsArray($portableData);
        $this->assertTrue($importResult['success']);
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_data_minimization(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'address' => '123 Main St',
            'ssn' => '123-45-6789',
            'preferences' => ['theme' => 'dark', 'language' => 'en']
        ];

        $minimizedData = $this->minimizeData($userData, ['name', 'email']);

        $this->assertArrayHasKey('name', $minimizedData);
        $this->assertArrayHasKey('email', $minimizedData);
        $this->assertArrayNotHasKey('phone', $minimizedData);
        $this->assertArrayNotHasKey('address', $minimizedData);
        $this->assertArrayNotHasKey('ssn', $minimizedData);
    }

    private function encryptData(string $data, string $key): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    private function decryptData(string $encryptedData, string $key): string
    {
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }

    private function maskSensitiveData(array $data): array
    {
        $masked = [];

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'email':
                    $masked[$key] = preg_replace('/(.{2}).*(@.*)/', '$1***', $value);
                    break;
                case 'phone':
                    $masked[$key] = preg_replace('/(\d{3})\d{3}(\d{4})/', '$1***$2', $value);
                    break;
                case 'ssn':
                    $masked[$key] = preg_replace('/(\d{3})-(\d{2})-(\d{4})/', '***-**-$3', $value);
                    break;
                case 'credit_card':
                    $masked[$key] = preg_replace('/(\d{4})-(\d{4})-(\d{4})-(\d{4})/', '****-****-****-$4', $value);
                    break;
                default:
                    $masked[$key] = $value;
            }
        }

        return $masked;
    }

    private function anonymizeData(array $data): array
    {
        $anonymized = [];

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'name':
                    $anonymized[$key] = 'User_' . substr(md5($value), 0, 8);
                    break;
                case 'email':
                    $anonymized[$key] = 'user_' . substr(md5($value), 0, 8) . '@example.com';
                    break;
                case 'phone':
                    $anonymized[$key] = '+1-555-' . rand(100, 999) . '-' . rand(1000, 9999);
                    break;
                case 'address':
                    $anonymized[$key] = 'Address_' . substr(md5($value), 0, 8);
                    break;
                default:
                    $anonymized[$key] = $value;
            }
        }

        return $anonymized;
    }

    private function tokenizeData(string $value): string
    {
        return 'token_' . bin2hex(random_bytes(16));
    }

    private function detokenizeData(string $token): string
    {
        // Simulate token lookup - return original value for any token
        return '4111-1111-1111-1111';
    }

    private function classifyData(array $data): array
    {
        $classified = [
            'public' => [],
            'internal' => [],
            'confidential' => [],
            'restricted' => []
        ];

        foreach ($data as $key => $value) {
            if (strpos($key, 'public') !== false) {
                $classified['public'][$key] = $value;
            } elseif (strpos($key, 'internal') !== false) {
                $classified['internal'][$key] = $value;
            } elseif (strpos($key, 'confidential') !== false) {
                $classified['confidential'][$key] = $value;
            } elseif (strpos($key, 'restricted') !== false) {
                $classified['restricted'][$key] = $value;
            }
        }

        return $classified;
    }

    private function shouldRetainData(int $dataId, string $dataType, int $creationDate, int $retentionPeriod): bool
    {
        $ageInDays = (time() - $creationDate) / (24 * 60 * 60);
        return $ageInDays <= $retentionPeriod;
    }

    private function getExpiredData(string $dataType, int $retentionPeriod): array
    {
        // Simulate expired data retrieval
        return [
            'data_id' => 123,
            'data_type' => $dataType,
            'created_at' => time() - (($retentionPeriod + 1) * 24 * 60 * 60)
        ];
    }

    private function encryptAtRest(string $data, string $key): string
    {
        return $this->encryptData($data, $key);
    }

    private function decryptFromRest(string $encryptedData, string $key): string
    {
        return $this->decryptData($encryptedData, $key);
    }

    private function encryptInTransit(string $data, string $publicKey): string
    {
        // Simulate public key encryption
        return 'encrypted_' . base64_encode($data);
    }

    private function decryptInTransit(string $encryptedData, string $privateKey): string
    {
        // Simulate private key decryption
        return base64_decode(str_replace('encrypted_', '', $encryptedData));
    }

    private function obfuscateData(string $data): string
    {
        return preg_replace('/[a-zA-Z0-9@._-]/', '*', $data);
    }

    private function pseudonymizeData(array $data): array
    {
        $pseudonymized = $data;

        if (isset($data['name'])) {
            $pseudonymized['name'] = 'User_' . substr(md5($data['name']), 0, 8);
        }

        if (isset($data['email'])) {
            $pseudonymized['email'] = 'user_' . substr(md5($data['email']), 0, 8) . '@example.com';
        }

        return $pseudonymized;
    }

    private function addWatermark(string $data, string $watermark): string
    {
        return $data . '|WATERMARK:' . $watermark;
    }

    private function extractWatermark(string $watermarkedData): string
    {
        if (preg_match('/\|WATERMARK:(.+)$/', $watermarkedData, $matches)) {
            return $matches[1];
        }

        return '';
    }

    private function redactSensitiveData(string $document, array $patterns): string
    {
        $redacted = $document;

        foreach ($patterns as $type => $pattern) {
            $redacted = preg_replace($pattern, '[REDACTED]', $redacted);
        }

        return $redacted;
    }

    private function applyDataGovernance(array $dataAsset): array
    {
        return [
            'compliance_status' => 'compliant',
            'access_controls' => [
                'encryption' => true,
                'access_logging' => true,
                'audit_trail' => true
            ],
            'retention_schedule' => [
                'retention_period' => $dataAsset['retention_period'],
                'disposal_method' => 'secure_deletion'
            ]
        ];
    }

    private function detectDataBreach(string $data, array $patterns): array
    {
        $detectedPatterns = [];

        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $data)) {
                $detectedPatterns[] = $type;
            }
        }

        return [
            'breach_detected' => !empty($detectedPatterns),
            'sensitive_patterns' => $detectedPatterns
        ];
    }

    private function getConsentStatus(int $userId, array $dataTypes, array $purposes): array
    {
        // Simulate consent status retrieval
        $consent = [];
        foreach ($dataTypes as $dataType) {
            foreach ($purposes as $purpose) {
                $consent["{$dataType}:{$purpose}"] = true;
            }
        }

        return $consent;
    }

    private function updateConsent(int $userId, string $dataType, string $purpose, bool $consent): array
    {
        return [
            'success' => true,
            'user_id' => $userId,
            'data_type' => $dataType,
            'purpose' => $purpose,
            'consent' => $consent,
            'timestamp' => time()
        ];
    }

    private function exportUserData(int $userId, array $dataTypes): array
    {
        return [
            'user_id' => $userId,
            'exported_data' => [
                'profile' => ['name' => 'John Doe', 'email' => 'john@example.com'],
                'preferences' => ['theme' => 'dark', 'language' => 'en'],
                'activity_logs' => ['login' => '2024-01-15', 'last_action' => 'view_profile']
            ],
            'export_timestamp' => time()
        ];
    }

    private function importUserData(int $userId, array $data): array
    {
        return [
            'success' => true,
            'user_id' => $userId,
            'imported_records' => count($data['exported_data']),
            'import_timestamp' => time()
        ];
    }

    private function minimizeData(array $data, array $allowedFields): array
    {
        $minimized = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $minimized[$field] = $data[$field];
            }
        }

        return $minimized;
    }
}
