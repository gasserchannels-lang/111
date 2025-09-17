<?php

namespace Tests\Unit\DataQuality;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DataValidityTest extends TestCase
{
    #[Test]
    public function it_validates_email_format(): void
    {
        $validEmails = [
            'user@example.com',
            'test.email@domain.co.uk',
            'user+tag@example.org'
        ];

        foreach ($validEmails as $email) {
            $this->assertTrue($this->isValidEmail($email));
        }
    }

    #[Test]
    public function it_rejects_invalid_email_format(): void
    {
        $invalidEmails = [
            'invalid-email',
            '@example.com',
            'user@',
            'user..double.dot@example.com'
        ];

        foreach ($invalidEmails as $email) {
            $this->assertFalse($this->isValidEmail($email));
        }
    }

    #[Test]
    public function it_validates_phone_number_format(): void
    {
        $validPhones = [
            '+1234567890',
            '(555) 123-4567',
            '555-123-4567',
            '555.123.4567'
        ];

        foreach ($validPhones as $phone) {
            $this->assertTrue($this->isValidPhone($phone));
        }
    }

    #[Test]
    public function it_validates_url_format(): void
    {
        $validUrls = [
            'https://www.example.com',
            'http://example.com',
            'https://subdomain.example.com/path',
            'https://example.com:8080/path?param=value'
        ];

        foreach ($validUrls as $url) {
            $this->assertTrue($this->isValidUrl($url));
        }
    }

    #[Test]
    public function it_validates_date_format(): void
    {
        $validDates = [
            '2024-01-15',
            '2024/01/15',
            '15-01-2024',
            'January 15, 2024'
        ];

        foreach ($validDates as $date) {
            $this->assertTrue($this->isValidDate($date));
        }
    }

    #[Test]
    public function it_validates_numeric_ranges(): void
    {
        $this->assertTrue($this->isInRange(50, 0, 100));
        $this->assertTrue($this->isInRange(0, 0, 100));
        $this->assertTrue($this->isInRange(100, 0, 100));
        $this->assertFalse($this->isInRange(150, 0, 100));
        $this->assertFalse($this->isInRange(-10, 0, 100));
    }

    #[Test]
    public function it_validates_string_length(): void
    {
        $this->assertTrue($this->isValidLength('Hello', 1, 10));
        $this->assertTrue($this->isValidLength('A', 1, 10));
        $this->assertTrue($this->isValidLength('Hello World', 1, 20));
        $this->assertFalse($this->isValidLength('', 1, 10));
        $this->assertFalse($this->isValidLength('This is a very long string', 1, 10));
    }

    #[Test]
    public function it_validates_required_fields(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 30
        ];

        $requiredFields = ['name', 'email', 'age'];

        $this->assertTrue($this->hasRequiredFields($data, $requiredFields));
    }

    #[Test]
    public function it_detects_missing_required_fields(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
            // age is missing
        ];

        $requiredFields = ['name', 'email', 'age'];

        $this->assertFalse($this->hasRequiredFields($data, $requiredFields));
    }

    private function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function isValidPhone(string $phone): bool
    {
        $pattern = '/^[\+]?[1-9][\d]{0,15}$/';
        $cleanPhone = preg_replace('/[^\d\+]/', '', $phone);
        return preg_match($pattern, $cleanPhone) === 1;
    }

    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private function isValidDate(string $date): bool
    {
        $formats = ['Y-m-d', 'Y/m/d', 'd-m-Y', 'd/m/Y', 'F j, Y'];

        foreach ($formats as $format) {
            $parsed = \DateTime::createFromFormat($format, $date);
            if ($parsed && $parsed->format($format) === $date) {
                return true;
            }
        }

        return false;
    }

    private function isInRange($value, $min, $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    private function isValidLength(string $string, int $minLength, int $maxLength): bool
    {
        $length = strlen($string);
        return $length >= $minLength && $length <= $maxLength;
    }

    private function hasRequiredFields(array $data, array $requiredFields): bool
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }

        return true;
    }
}
