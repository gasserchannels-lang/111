<?php

namespace Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DataAnonymizationTest extends TestCase
{
    #[Test]
    public function it_anonymizes_personal_data(): void
    {
        $personalData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1-555-123-4567',
            'address' => '123 Main St, New York, NY 10001'
        ];

        $anonymizedData = $this->anonymizePersonalData($personalData);

        $this->assertNotEquals('John Doe', $anonymizedData['name']);
        $this->assertNotEquals('john.doe@example.com', $anonymizedData['email']);
        $this->assertNotEquals('+1-555-123-4567', $anonymizedData['phone']);
        $this->assertNotEquals('123 Main St, New York, NY 10001', $anonymizedData['address']);
    }

    #[Test]
    public function it_anonymizes_user_identifiers(): void
    {
        $userData = [
            'user_id' => 12345,
            'username' => 'johndoe',
            'session_id' => 'sess_abc123def456',
            'ip_address' => '192.168.1.100'
        ];

        $anonymizedData = $this->anonymizeUserIdentifiers($userData);

        $this->assertNotEquals(12345, $anonymizedData['user_id']);
        $this->assertNotEquals('johndoe', $anonymizedData['username']);
        $this->assertNotEquals('sess_abc123def456', $anonymizedData['session_id']);
        $this->assertNotEquals('192.168.1.100', $anonymizedData['ip_address']);
    }

    #[Test]
    public function it_anonymizes_financial_data(): void
    {
        $financialData = [
            'credit_card' => '4111-1111-1111-1111',
            'bank_account' => '1234567890',
            'routing_number' => '021000021',
            'ssn' => '123-45-6789'
        ];

        $anonymizedData = $this->anonymizeFinancialData($financialData);

        $this->assertNotEquals('4111-1111-1111-1111', $anonymizedData['credit_card']);
        $this->assertNotEquals('1234567890', $anonymizedData['bank_account']);
        $this->assertNotEquals('021000021', $anonymizedData['routing_number']);
        $this->assertNotEquals('123-45-6789', $anonymizedData['ssn']);
    }

    #[Test]
    public function it_anonymizes_health_data(): void
    {
        $healthData = [
            'medical_record_id' => 'MR123456789',
            'diagnosis' => 'Hypertension',
            'medication' => 'Lisinopril 10mg',
            'doctor_name' => 'Dr. Smith'
        ];

        $anonymizedData = $this->anonymizeHealthData($healthData);

        $this->assertNotEquals('MR123456789', $anonymizedData['medical_record_id']);
        $this->assertNotEquals('Hypertension', $anonymizedData['diagnosis']);
        $this->assertNotEquals('Lisinopril 10mg', $anonymizedData['medication']);
        $this->assertNotEquals('Dr. Smith', $anonymizedData['doctor_name']);
    }

    #[Test]
    public function it_anonymizes_location_data(): void
    {
        $locationData = [
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'address' => '123 Main St, New York, NY 10001',
            'zip_code' => '10001'
        ];

        $anonymizedData = $this->anonymizeLocationData($locationData);

        $this->assertNotEquals(40.7128, $anonymizedData['latitude']);
        $this->assertNotEquals(-74.0060, $anonymizedData['longitude']);
        $this->assertNotEquals('123 Main St, New York, NY 10001', $anonymizedData['address']);
        $this->assertNotEquals('10001', $anonymizedData['zip_code']);
    }

    #[Test]
    public function it_anonymizes_biometric_data(): void
    {
        $biometricData = [
            'fingerprint_hash' => 'abc123def456ghi789',
            'face_template' => 'face_template_data_123',
            'voice_print' => 'voice_print_data_456',
            'iris_scan' => 'iris_scan_data_789'
        ];

        $anonymizedData = $this->anonymizeBiometricData($biometricData);

        $this->assertNotEquals('abc123def456ghi789', $anonymizedData['fingerprint_hash']);
        $this->assertNotEquals('face_template_data_123', $anonymizedData['face_template']);
        $this->assertNotEquals('voice_print_data_456', $anonymizedData['voice_print']);
        $this->assertNotEquals('iris_scan_data_789', $anonymizedData['iris_scan']);
    }

    #[Test]
    public function it_anonymizes_communication_data(): void
    {
        $communicationData = [
            'message_content' => 'This is a private message',
            'sender_id' => 12345,
            'recipient_id' => 67890,
            'timestamp' => '2024-01-15 10:30:00'
        ];

        $anonymizedData = $this->anonymizeCommunicationData($communicationData);

        $this->assertNotEquals('This is a private message', $anonymizedData['message_content']);
        $this->assertNotEquals(12345, $anonymizedData['sender_id']);
        $this->assertNotEquals(67890, $anonymizedData['recipient_id']);
        $this->assertNotEquals('2024-01-15 10:30:00', $anonymizedData['timestamp']);
    }

    #[Test]
    public function it_anonymizes_browsing_data(): void
    {
        $browsingData = [
            'url' => 'https://example.com/private-page',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'referrer' => 'https://google.com/search?q=private+search',
            'cookies' => 'session_id=abc123; user_pref=xyz789'
        ];

        $anonymizedData = $this->anonymizeBrowsingData($browsingData);

        $this->assertNotEquals('https://example.com/private-page', $anonymizedData['url']);
        $this->assertNotEquals('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', $anonymizedData['user_agent']);
        $this->assertNotEquals('https://google.com/search?q=private+search', $anonymizedData['referrer']);
        $this->assertNotEquals('session_id=abc123; user_pref=xyz789', $anonymizedData['cookies']);
    }

    #[Test]
    public function it_anonymizes_purchase_data(): void
    {
        $purchaseData = [
            'order_id' => 'ORD-123456789',
            'product_name' => 'iPhone 15 Pro Max',
            'price' => 1199.00,
            'payment_method' => 'Credit Card ending in 1234'
        ];

        $anonymizedData = $this->anonymizePurchaseData($purchaseData);

        $this->assertNotEquals('ORD-123456789', $anonymizedData['order_id']);
        $this->assertNotEquals('iPhone 15 Pro Max', $anonymizedData['product_name']);
        $this->assertNotEquals(1199.00, $anonymizedData['price']);
        $this->assertNotEquals('Credit Card ending in 1234', $anonymizedData['payment_method']);
    }

    #[Test]
    public function it_anonymizes_social_data(): void
    {
        $socialData = [
            'social_security_number' => '123-45-6789',
            'drivers_license' => 'DL123456789',
            'passport_number' => 'P123456789',
            'date_of_birth' => '1990-01-15'
        ];

        $anonymizedData = $this->anonymizeSocialData($socialData);

        $this->assertNotEquals('123-45-6789', $anonymizedData['social_security_number']);
        $this->assertNotEquals('DL123456789', $anonymizedData['drivers_license']);
        $this->assertNotEquals('P123456789', $anonymizedData['passport_number']);
        $this->assertNotEquals('1990-01-15', $anonymizedData['date_of_birth']);
    }

    #[Test]
    public function it_anonymizes_workplace_data(): void
    {
        $workplaceData = [
            'employee_id' => 'EMP123456',
            'salary' => 75000.00,
            'department' => 'Engineering',
            'manager_name' => 'Jane Smith'
        ];

        $anonymizedData = $this->anonymizeWorkplaceData($workplaceData);

        $this->assertNotEquals('EMP123456', $anonymizedData['employee_id']);
        $this->assertNotEquals(75000.00, $anonymizedData['salary']);
        $this->assertNotEquals('Engineering', $anonymizedData['department']);
        $this->assertNotEquals('Jane Smith', $anonymizedData['manager_name']);
    }

    #[Test]
    public function it_anonymizes_educational_data(): void
    {
        $educationalData = [
            'student_id' => 'STU123456',
            'gpa' => 3.75,
            'major' => 'Computer Science',
            'university' => 'University of California'
        ];

        $anonymizedData = $this->anonymizeEducationalData($educationalData);

        $this->assertNotEquals('STU123456', $anonymizedData['student_id']);
        $this->assertNotEquals(3.75, $anonymizedData['gpa']);
        $this->assertNotEquals('Computer Science', $anonymizedData['major']);
        $this->assertNotEquals('University of California', $anonymizedData['university']);
    }

    #[Test]
    public function it_anonymizes_legal_data(): void
    {
        $legalData = [
            'case_number' => 'CASE-123456789',
            'client_name' => 'John Doe',
            'attorney_name' => 'Jane Smith',
            'case_details' => 'Confidential legal matter'
        ];

        $anonymizedData = $this->anonymizeLegalData($legalData);

        $this->assertNotEquals('CASE-123456789', $anonymizedData['case_number']);
        $this->assertNotEquals('John Doe', $anonymizedData['client_name']);
        $this->assertNotEquals('Jane Smith', $anonymizedData['attorney_name']);
        $this->assertNotEquals('Confidential legal matter', $anonymizedData['case_details']);
    }

    #[Test]
    public function it_anonymizes_government_data(): void
    {
        $governmentData = [
            'tax_id' => '12-3456789',
            'voter_id' => 'V123456789',
            'license_plate' => 'ABC123',
            'government_id' => 'GOV123456789'
        ];

        $anonymizedData = $this->anonymizeGovernmentData($governmentData);

        $this->assertNotEquals('12-3456789', $anonymizedData['tax_id']);
        $this->assertNotEquals('V123456789', $anonymizedData['voter_id']);
        $this->assertNotEquals('ABC123', $anonymizedData['license_plate']);
        $this->assertNotEquals('GOV123456789', $anonymizedData['government_id']);
    }

    #[Test]
    public function it_anonymizes_children_data(): void
    {
        $childrenData = [
            'child_name' => 'Little John',
            'age' => 8,
            'school' => 'Elementary School',
            'parent_contact' => 'parent@example.com'
        ];

        $anonymizedData = $this->anonymizeChildrenData($childrenData);

        $this->assertNotEquals('Little John', $anonymizedData['child_name']);
        $this->assertNotEquals(8, $anonymizedData['age']);
        $this->assertNotEquals('Elementary School', $anonymizedData['school']);
        $this->assertNotEquals('parent@example.com', $anonymizedData['parent_contact']);
    }

    #[Test]
    public function it_anonymizes_sensitive_categories(): void
    {
        $sensitiveData = [
            'race' => 'African American',
            'religion' => 'Christian',
            'political_affiliation' => 'Democrat',
            'sexual_orientation' => 'Heterosexual'
        ];

        $anonymizedData = $this->anonymizeSensitiveCategories($sensitiveData);

        $this->assertNotEquals('African American', $anonymizedData['race']);
        $this->assertNotEquals('Christian', $anonymizedData['religion']);
        $this->assertNotEquals('Democrat', $anonymizedData['political_affiliation']);
        $this->assertNotEquals('Heterosexual', $anonymizedData['sexual_orientation']);
    }

    #[Test]
    public function it_anonymizes_with_preserved_structure(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'age' => 30,
            'active' => true
        ];

        $anonymizedData = $this->anonymizeWithPreservedStructure($data);

        // Structure should be preserved
        $this->assertArrayHasKey('name', $anonymizedData);
        $this->assertArrayHasKey('email', $anonymizedData);
        $this->assertArrayHasKey('age', $anonymizedData);
        $this->assertArrayHasKey('active', $anonymizedData);

        // Data types should be preserved
        $this->assertIsString($anonymizedData['name']);
        $this->assertIsString($anonymizedData['email']);
        $this->assertIsInt($anonymizedData['age']);
        $this->assertIsBool($anonymizedData['active']);
    }

    #[Test]
    public function it_anonymizes_with_reversible_encryption(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com'
        ];

        $anonymizedData = $this->anonymizeWithReversibleEncryption($data);
        $deidentifiedData = $this->deidentifyWithReversibleEncryption($anonymizedData);

        $this->assertEquals($data, $deidentifiedData);
    }

    private function anonymizePersonalData(array $data): array
    {
        return [
            'name' => $this->anonymizeString($data['name']),
            'email' => $this->anonymizeEmail($data['email']),
            'phone' => $this->anonymizePhone($data['phone']),
            'address' => $this->anonymizeString($data['address'])
        ];
    }

    private function anonymizeUserIdentifiers(array $data): array
    {
        return [
            'user_id' => $this->anonymizeNumber($data['user_id']),
            'username' => $this->anonymizeString($data['username']),
            'session_id' => $this->anonymizeString($data['session_id']),
            'ip_address' => $this->anonymizeIP($data['ip_address'])
        ];
    }

    private function anonymizeFinancialData(array $data): array
    {
        return [
            'credit_card' => $this->anonymizeCreditCard($data['credit_card']),
            'bank_account' => $this->anonymizeNumber($data['bank_account']),
            'routing_number' => $this->anonymizeNumber($data['routing_number']),
            'ssn' => $this->anonymizeSSN($data['ssn'])
        ];
    }

    private function anonymizeHealthData(array $data): array
    {
        return [
            'medical_record_id' => $this->anonymizeString($data['medical_record_id']),
            'diagnosis' => $this->anonymizeString($data['diagnosis']),
            'medication' => $this->anonymizeString($data['medication']),
            'doctor_name' => $this->anonymizeString($data['doctor_name'])
        ];
    }

    private function anonymizeLocationData(array $data): array
    {
        return [
            'latitude' => $this->anonymizeCoordinate($data['latitude']),
            'longitude' => $this->anonymizeCoordinate($data['longitude']),
            'address' => $this->anonymizeString($data['address']),
            'zip_code' => $this->anonymizeString($data['zip_code'])
        ];
    }

    private function anonymizeBiometricData(array $data): array
    {
        return [
            'fingerprint_hash' => $this->anonymizeString($data['fingerprint_hash']),
            'face_template' => $this->anonymizeString($data['face_template']),
            'voice_print' => $this->anonymizeString($data['voice_print']),
            'iris_scan' => $this->anonymizeString($data['iris_scan'])
        ];
    }

    private function anonymizeCommunicationData(array $data): array
    {
        return [
            'message_content' => $this->anonymizeString($data['message_content']),
            'sender_id' => $this->anonymizeNumber($data['sender_id']),
            'recipient_id' => $this->anonymizeNumber($data['recipient_id']),
            'timestamp' => $this->anonymizeTimestamp($data['timestamp'])
        ];
    }

    private function anonymizeBrowsingData(array $data): array
    {
        return [
            'url' => $this->anonymizeURL($data['url']),
            'user_agent' => $this->anonymizeString($data['user_agent']),
            'referrer' => $this->anonymizeURL($data['referrer']),
            'cookies' => $this->anonymizeString($data['cookies'])
        ];
    }

    private function anonymizePurchaseData(array $data): array
    {
        return [
            'order_id' => $this->anonymizeString($data['order_id']),
            'product_name' => $this->anonymizeString($data['product_name']),
            'price' => $this->anonymizePrice($data['price']),
            'payment_method' => $this->anonymizeString($data['payment_method'])
        ];
    }

    private function anonymizeSocialData(array $data): array
    {
        return [
            'social_security_number' => $this->anonymizeSSN($data['social_security_number']),
            'drivers_license' => $this->anonymizeString($data['drivers_license']),
            'passport_number' => $this->anonymizeString($data['passport_number']),
            'date_of_birth' => $this->anonymizeDate($data['date_of_birth'])
        ];
    }

    private function anonymizeWorkplaceData(array $data): array
    {
        return [
            'employee_id' => $this->anonymizeString($data['employee_id']),
            'salary' => $this->anonymizeSalary($data['salary']),
            'department' => $this->anonymizeString($data['department']),
            'manager_name' => $this->anonymizeString($data['manager_name'])
        ];
    }

    private function anonymizeEducationalData(array $data): array
    {
        return [
            'student_id' => $this->anonymizeString($data['student_id']),
            'gpa' => $this->anonymizeGPA($data['gpa']),
            'major' => $this->anonymizeString($data['major']),
            'university' => $this->anonymizeString($data['university'])
        ];
    }

    private function anonymizeLegalData(array $data): array
    {
        return [
            'case_number' => $this->anonymizeString($data['case_number']),
            'client_name' => $this->anonymizeString($data['client_name']),
            'attorney_name' => $this->anonymizeString($data['attorney_name']),
            'case_details' => $this->anonymizeString($data['case_details'])
        ];
    }

    private function anonymizeGovernmentData(array $data): array
    {
        return [
            'tax_id' => $this->anonymizeString($data['tax_id']),
            'voter_id' => $this->anonymizeString($data['voter_id']),
            'license_plate' => $this->anonymizeString($data['license_plate']),
            'government_id' => $this->anonymizeString($data['government_id'])
        ];
    }

    private function anonymizeChildrenData(array $data): array
    {
        return [
            'child_name' => $this->anonymizeString($data['child_name']),
            'age' => $this->anonymizeAge($data['age']),
            'school' => $this->anonymizeString($data['school']),
            'parent_contact' => $this->anonymizeEmail($data['parent_contact'])
        ];
    }

    private function anonymizeSensitiveCategories(array $data): array
    {
        return [
            'race' => $this->anonymizeString($data['race']),
            'religion' => $this->anonymizeString($data['religion']),
            'political_affiliation' => $this->anonymizeString($data['political_affiliation']),
            'sexual_orientation' => $this->anonymizeString($data['sexual_orientation'])
        ];
    }

    private function anonymizeWithPreservedStructure(array $data): array
    {
        $anonymized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $anonymized[$key] = $this->anonymizeString($value);
            } elseif (is_int($value)) {
                $anonymized[$key] = $this->anonymizeNumber($value);
            } elseif (is_bool($value)) {
                $anonymized[$key] = $value; // Keep boolean values as is
            } else {
                $anonymized[$key] = $value;
            }
        }
        return $anonymized;
    }

    private function anonymizeWithReversibleEncryption(array $data): array
    {
        $anonymized = [];
        foreach ($data as $key => $value) {
            $anonymized[$key] = base64_encode($value); // Simple reversible encryption
        }
        return $anonymized;
    }

    private function deidentifyWithReversibleEncryption(array $data): array
    {
        $deidentified = [];
        foreach ($data as $key => $value) {
            $deidentified[$key] = base64_decode($value); // Simple reversible decryption
        }
        return $deidentified;
    }

    // Helper methods for different anonymization techniques
    private function anonymizeString(string $value): string
    {
        return 'ANONYMIZED_' . substr(md5($value), 0, 8);
    }

    private function anonymizeEmail(string $email): string
    {
        $parts = explode('@', $email);
        return 'user_' . substr(md5($parts[0]), 0, 6) . '@' . $parts[1];
    }

    private function anonymizePhone(string $phone): string
    {
        return 'XXX-XXX-' . substr($phone, -4);
    }

    private function anonymizeNumber($number): int
    {
        return rand(100000, 999999);
    }

    private function anonymizeIP(string $ip): string
    {
        $parts = explode('.', $ip);
        return $parts[0] . '.' . $parts[1] . '.XXX.XXX';
    }

    private function anonymizeCreditCard(string $card): string
    {
        return 'XXXX-XXXX-XXXX-' . substr($card, -4);
    }

    private function anonymizeSSN(string $ssn): string
    {
        return 'XXX-XX-' . substr($ssn, -4);
    }

    private function anonymizeCoordinate(float $coord): float
    {
        return $coord + (rand(-100, 100) / 1000);
    }

    private function anonymizeTimestamp(string $timestamp): string
    {
        $date = new \DateTime($timestamp);
        $date->modify('+' . rand(-30, 30) . ' days');
        return $date->format('Y-m-d H:i:s');
    }

    private function anonymizeURL(string $url): string
    {
        $parsed = parse_url($url);
        return $parsed['scheme'] . '://' . $parsed['host'] . '/anonymized-path';
    }

    private function anonymizePrice(float $price): float
    {
        return round($price * (0.8 + (rand(0, 40) / 100)), 2);
    }

    private function anonymizeDate(string $date): string
    {
        $dateObj = new \DateTime($date);
        $dateObj->modify('+' . rand(-365, 365) . ' days');
        return $dateObj->format('Y-m-d');
    }

    private function anonymizeSalary(float $salary): float
    {
        return round($salary * (0.9 + (rand(0, 20) / 100)), 2);
    }

    private function anonymizeGPA(float $gpa): float
    {
        return round($gpa * (0.8 + (rand(0, 40) / 100)), 2);
    }

    private function anonymizeAge(int $age): int
    {
        return $age + rand(-5, 5);
    }
}
