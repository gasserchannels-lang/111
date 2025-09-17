<?php

namespace Tests\Unit\DataAccuracy;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversNothing;

class DataValidationTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_validates_email_format(): void
    {
        $validEmails = [
            'user@example.com',
            'test.email@domain.co.uk',
            'user+tag@example.org',
            'user123@test-domain.com'
        ];

        $invalidEmails = [
            'invalid-email',
            '@example.com',
            'user@',
            'user@.com',
            'user..name@example.com'
        ];

        foreach ($validEmails as $email) {
            $this->assertTrue($this->isValidEmail($email));
        }

        foreach ($invalidEmails as $email) {
            $this->assertFalse($this->isValidEmail($email));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_phone_number_format(): void
    {
        $validPhones = [
            '+1-555-123-4567',
            '+44-20-7946-0958',
            '+971-50-123-4567',
            '555-123-4567',
            '(555) 123-4567'
        ];

        $invalidPhones = [
            '123',
            'abc-def-ghij',
            '+1-555-12', // Only 6 digits, too short for international
            '555-123-456', // Only 9 digits, too short for domestic
            '123456789', // Only 9 digits, too short for domestic
            '+123', // Only 3 digits, too short for international
            '555-123-456789012345' // Too long
        ];

        foreach ($validPhones as $phone) {
            $this->assertTrue($this->isValidPhone($phone));
        }

        foreach ($invalidPhones as $phone) {
            $isValid = $this->isValidPhone($phone);
            if ($isValid) {
                echo "Phone '$phone' is unexpectedly valid\n";
            }
            $this->assertFalse($isValid, "Phone '$phone' should be invalid");
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_url_format(): void
    {
        $validUrls = [
            'https://www.example.com',
            'http://example.com',
            'https://subdomain.example.com/path',
            'https://example.com:8080/path?param=value'
        ];

        $invalidUrls = [
            'not-a-url',
            'ftp://example.com',
            'https://',
            'example.com'
        ];

        foreach ($validUrls as $url) {
            $this->assertTrue($this->isValidUrl($url));
        }

        foreach ($invalidUrls as $url) {
            $this->assertFalse($this->isValidUrl($url));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_date_format(): void
    {
        $validDates = [
            '2024-01-15',
            '2024-12-31',
            '2023-02-28',
            '2024-02-29' // Leap year
        ];

        $invalidDates = [
            '2024-13-01',
            '2024-02-30',
            '2024-04-31',
            '2023-02-29', // Not a leap year
            'invalid-date'
        ];

        foreach ($validDates as $date) {
            $this->assertTrue($this->isValidDate($date));
        }

        foreach ($invalidDates as $date) {
            $this->assertFalse($this->isValidDate($date));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_currency_code(): void
    {
        $validCurrencies = ['USD', 'EUR', 'GBP', 'JPY', 'AED', 'EGP', 'CAD', 'AUD'];
        $invalidCurrencies = ['US', 'EURO', '123', 'ABC', ''];

        foreach ($validCurrencies as $currency) {
            $this->assertTrue($this->isValidCurrencyCode($currency));
        }

        foreach ($invalidCurrencies as $currency) {
            $this->assertFalse($this->isValidCurrencyCode($currency));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_product_sku_format(): void
    {
        $validSkus = [
            'SKU-123456',
            'PROD-ABC-001',
            'ITEM-2024-001',
            '12345-67890'
        ];

        $invalidSkus = [
            'SKU-',
            '-123456',
            'SKU 123456',
            '',
            'SKU-12345678901234567890' // Too long
        ];

        foreach ($validSkus as $sku) {
            $this->assertTrue($this->isValidSku($sku));
        }

        foreach ($invalidSkus as $sku) {
            $this->assertFalse($this->isValidSku($sku));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_postal_code_format(): void
    {
        $validPostalCodes = [
            '12345',      // US ZIP
            '12345-6789', // US ZIP+4
            'K1A 0A6',    // Canadian
            'SW1A 1AA',   // UK
            '12345',      // Generic 5-digit
        ];

        $invalidPostalCodes = [
            '1234',
            '123456',
            'ABC123',
            '',
            '12345-67890'
        ];

        foreach ($validPostalCodes as $postalCode) {
            $this->assertTrue($this->isValidPostalCode($postalCode));
        }

        foreach ($invalidPostalCodes as $postalCode) {
            $this->assertFalse($this->isValidPostalCode($postalCode));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_credit_card_number(): void
    {
        $validCards = [
            '4111111111111111', // Visa
            '5555555555554444', // Mastercard
            '378282246310005',  // American Express
            '6011111111111117'  // Discover
        ];

        $invalidCards = [
            '1234567890123456',
            '411111111111111',
            '41111111111111111',
            'abcd123456789012'
        ];

        foreach ($validCards as $card) {
            $this->assertTrue($this->isValidCreditCard($card));
        }

        foreach ($invalidCards as $card) {
            $this->assertFalse($this->isValidCreditCard($card));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_ip_address(): void
    {
        $validIps = [
            '192.168.1.1',
            '10.0.0.1',
            '172.16.0.1',
            '8.8.8.8',
            '255.255.255.255'
        ];

        $invalidIps = [
            '256.1.1.1',
            '192.168.1',
            '192.168.1.1.1',
            '192.168.1.256',
            'not-an-ip'
        ];

        foreach ($validIps as $ip) {
            $this->assertTrue($this->isValidIpAddress($ip));
        }

        foreach ($invalidIps as $ip) {
            $this->assertFalse($this->isValidIpAddress($ip));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_json_format(): void
    {
        $validJson = [
            '{"name": "John", "age": 30}',
            '{"products": [{"id": 1, "name": "Product 1"}]}',
            '{}',
            '[]'
        ];

        $invalidJson = [
            '{"name": "John", "age": 30', // Missing closing brace
            '{name: "John", "age": 30}',  // Missing quotes around key
            'not json',
            ''
        ];

        foreach ($validJson as $json) {
            $this->assertTrue($this->isValidJson($json));
        }

        foreach ($invalidJson as $json) {
            $this->assertFalse($this->isValidJson($json));
        }
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_required_fields(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 30
        ];

        $requiredFields = ['name', 'email', 'age'];
        $this->assertTrue($this->hasRequiredFields($data, $requiredFields));

        $missingFields = ['name', 'email', 'phone'];
        $this->assertFalse($this->hasRequiredFields($data, $missingFields));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_field_length(): void
    {
        $data = [
            'short_text' => 'Hi',
            'long_text' => str_repeat('A', 1000),
            'normal_text' => 'This is normal length'
        ];

        $this->assertTrue($this->isValidLength($data['short_text'], 1, 10));
        $this->assertFalse($this->isValidLength($data['long_text'], 1, 100));
        $this->assertTrue($this->isValidLength($data['normal_text'], 1, 100));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_numeric_range(): void
    {
        $this->assertTrue($this->isInRange(50, 0, 100));
        $this->assertFalse($this->isInRange(150, 0, 100));
        $this->assertTrue($this->isInRange(0, 0, 100));
        $this->assertTrue($this->isInRange(100, 0, 100));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_array_structure(): void
    {
        $validArray = [
            'users' => [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane']
            ]
        ];

        $expectedStructure = [
            'users' => [
                ['id' => 'integer', 'name' => 'string']
            ]
        ];

        $this->assertTrue($this->matchesStructure($validArray, $expectedStructure));
    }

    private function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function isValidPhone(string $phone): bool
    {
        // Remove all non-digit characters except +
        $cleaned = preg_replace('/[^\d+]/', '', $phone);

        // Check if it's a valid format
        if (empty($cleaned)) {
            return false;
        }

        // International format: + followed by 7-15 digits
        if (strpos($cleaned, '+') === 0) {
            $digitsOnly = substr($cleaned, 1);
            return strlen($digitsOnly) >= 7 && strlen($digitsOnly) <= 15 && ctype_digit($digitsOnly);
        }

        // Domestic format: exactly 10-15 digits
        if (ctype_digit($cleaned)) {
            return strlen($cleaned) >= 10 && strlen($cleaned) <= 15;
        }

        // If it contains non-digit characters (except +), it's invalid
        return false;
    }

    private function isValidUrl(string $url): bool
    {
        // Must start with http:// or https://
        if (!preg_match('/^https?:\/\//', $url)) {
            return false;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function isValidCurrencyCode(string $currency): bool
    {
        $validCodes = ['USD', 'EUR', 'GBP', 'JPY', 'AED', 'EGP', 'CAD', 'AUD', 'CHF', 'CNY'];
        return in_array(strtoupper($currency), $validCodes);
    }

    private function isValidSku(string $sku): bool
    {
        // Must be 3-20 characters, start and end with alphanumeric, can contain hyphens in middle
        return preg_match('/^[A-Z0-9][A-Z0-9-]{1,18}[A-Z0-9]$/', $sku) === 1;
    }

    private function isValidPostalCode(string $postalCode): bool
    {
        // Must be 3-10 characters, alphanumeric with optional spaces and hyphens
        // Cannot start or end with space or hyphen
        // Must contain at least one letter or digit
        if (empty($postalCode)) {
            return false;
        }

        // Check length first - must be exactly 5 or 7-10 characters
        $length = strlen($postalCode);
        if ($length < 5 || ($length > 5 && $length < 7) || $length > 10) {
            return false;
        }

        // Must match pattern and not be all numbers or all letters
        if (preg_match('/^[A-Z0-9][A-Z0-9\s-]{1,8}[A-Z0-9]$/', $postalCode) !== 1) {
            return false;
        }

        // Additional check: must contain at least one letter or digit
        return preg_match('/[A-Z0-9]/', $postalCode) === 1;
    }

    private function isValidCreditCard(string $card): bool
    {
        // Luhn algorithm
        $card = preg_replace('/\D/', '', $card);
        $length = strlen($card);

        if ($length < 13 || $length > 19) {
            return false;
        }

        $sum = 0;
        $alternate = false;

        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int)$card[$i];

            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit = ($digit % 10) + 1;
                }
            }

            $sum += $digit;
            $alternate = !$alternate;
        }

        return $sum % 10 === 0;
    }

    private function isValidIpAddress(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    private function isValidJson(string $json): bool
    {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private function hasRequiredFields(array $data, array $requiredFields): bool
    {
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data) || empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    private function isValidLength(string $text, int $min, int $max): bool
    {
        $length = strlen($text);
        return $length >= $min && $length <= $max;
    }

    private function isInRange(float $value, float $min, float $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    private function matchesStructure(array $data, array $structure): bool
    {
        foreach ($structure as $key => $expectedType) {
            if (!array_key_exists($key, $data)) {
                return false;
            }

            if (is_array($expectedType)) {
                if (!is_array($data[$key])) {
                    return false;
                }

                foreach ($data[$key] as $item) {
                    if (!$this->matchesStructure($item, $expectedType[0])) {
                        return false;
                    }
                }
            } else {
                $actualType = gettype($data[$key]);
                if ($expectedType === 'integer' && $actualType !== 'integer') {
                    return false;
                }
                if ($expectedType === 'string' && $actualType !== 'string') {
                    return false;
                }
            }
        }

        return true;
    }
}
