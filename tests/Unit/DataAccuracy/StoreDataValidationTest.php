<?php

namespace Tests\Unit\DataAccuracy;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class StoreDataValidationTest extends TestCase
{
    #[Test]
    public function it_validates_store_name_format(): void
    {
        $validStoreNames = [
            'Amazon',
            'eBay',
            'Best Buy',
            'Walmart',
            'Target'
        ];

        foreach ($validStoreNames as $storeName) {
            $this->assertTrue($this->isValidStoreName($storeName));
        }
    }

    #[Test]
    public function it_validates_store_url_format(): void
    {
        $validUrls = [
            'https://amazon.com',
            'https://www.ebay.com',
            'https://bestbuy.com',
            'https://walmart.com',
            'https://target.com'
        ];

        foreach ($validUrls as $url) {
            $this->assertTrue($this->isValidStoreUrl($url));
        }
    }

    #[Test]
    public function it_validates_store_contact_information(): void
    {
        $storeData = [
            'name' => 'Test Store',
            'email' => 'contact@teststore.com',
            'phone' => '+1-555-123-4567',
            'address' => '123 Main St, City, State 12345'
        ];

        $this->assertTrue($this->validateStoreContactInfo($storeData));
    }

    #[Test]
    public function it_validates_store_rating_range(): void
    {
        $validRatings = [1.0, 2.5, 3.7, 4.2, 5.0];
        $invalidRatings = [0.0, 5.5, -1.0, 6.0];

        foreach ($validRatings as $rating) {
            $this->assertTrue($this->isValidRating($rating));
        }

        foreach ($invalidRatings as $rating) {
            $this->assertFalse($this->isValidRating($rating));
        }
    }

    #[Test]
    public function it_validates_store_currency_support(): void
    {
        $supportedCurrencies = ['USD', 'EUR', 'GBP', 'JPY', 'AED', 'EGP'];
        $storeData = [
            'currencies' => $supportedCurrencies
        ];

        $this->assertTrue($this->validateCurrencySupport($storeData));
    }

    #[Test]
    public function it_validates_store_shipping_options(): void
    {
        $shippingOptions = [
            ['name' => 'Standard', 'cost' => 5.99, 'days' => 5],
            ['name' => 'Express', 'cost' => 12.99, 'days' => 2],
            ['name' => 'Overnight', 'cost' => 24.99, 'days' => 1]
        ];

        $this->assertTrue($this->validateShippingOptions($shippingOptions));
    }

    #[Test]
    public function it_validates_store_payment_methods(): void
    {
        $paymentMethods = ['credit_card', 'paypal', 'apple_pay', 'google_pay'];
        $storeData = [
            'payment_methods' => $paymentMethods
        ];

        $this->assertTrue($this->validatePaymentMethods($storeData));
    }

    #[Test]
    public function it_validates_store_business_hours(): void
    {
        $businessHours = [
            'monday' => '9:00-17:00',
            'tuesday' => '9:00-17:00',
            'wednesday' => '9:00-17:00',
            'thursday' => '9:00-17:00',
            'friday' => '9:00-17:00',
            'saturday' => '10:00-16:00',
            'sunday' => 'closed'
        ];

        $this->assertTrue($this->validateBusinessHours($businessHours));
    }

    #[Test]
    public function it_validates_store_return_policy(): void
    {
        $returnPolicy = [
            'return_days' => 30,
            'refund_method' => 'original_payment',
            'restocking_fee' => 0.0,
            'conditions' => 'unused items only'
        ];

        $this->assertTrue($this->validateReturnPolicy($returnPolicy));
    }

    #[Test]
    public function it_validates_store_verification_status(): void
    {
        $verifiedStores = [
            ['name' => 'Amazon', 'verified' => true, 'verification_date' => '2024-01-15'],
            ['name' => 'eBay', 'verified' => true, 'verification_date' => '2024-02-20']
        ];

        foreach ($verifiedStores as $store) {
            $this->assertTrue($this->validateVerificationStatus($store));
        }
    }

    private function isValidStoreName(string $name): bool
    {
        return !empty($name) && strlen($name) >= 2 && strlen($name) <= 100;
    }

    private function isValidStoreUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private function validateStoreContactInfo(array $data): bool
    {
        return isset($data['name']) &&
            isset($data['email']) &&
            filter_var($data['email'], FILTER_VALIDATE_EMAIL) !== false &&
            isset($data['phone']) &&
            isset($data['address']);
    }

    private function isValidRating(float $rating): bool
    {
        return $rating >= 1.0 && $rating <= 5.0;
    }

    private function validateCurrencySupport(array $data): bool
    {
        $validCurrencies = ['USD', 'EUR', 'GBP', 'JPY', 'AED', 'EGP', 'CAD', 'AUD'];

        if (!isset($data['currencies']) || !is_array($data['currencies'])) {
            return false;
        }

        foreach ($data['currencies'] as $currency) {
            if (!in_array($currency, $validCurrencies)) {
                return false;
            }
        }

        return true;
    }

    private function validateShippingOptions(array $options): bool
    {
        foreach ($options as $option) {
            if (!isset($option['name']) || !isset($option['cost']) || !isset($option['days'])) {
                return false;
            }

            if ($option['cost'] < 0 || $option['days'] < 1) {
                return false;
            }
        }

        return true;
    }

    private function validatePaymentMethods(array $data): bool
    {
        $validMethods = ['credit_card', 'paypal', 'apple_pay', 'google_pay', 'bank_transfer'];

        if (!isset($data['payment_methods']) || !is_array($data['payment_methods'])) {
            return false;
        }

        foreach ($data['payment_methods'] as $method) {
            if (!in_array($method, $validMethods)) {
                return false;
            }
        }

        return true;
    }

    private function validateBusinessHours(array $hours): bool
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($days as $day) {
            if (!isset($hours[$day])) {
                return false;
            }
        }

        return true;
    }

    private function validateReturnPolicy(array $policy): bool
    {
        return isset($policy['return_days']) &&
            $policy['return_days'] > 0 &&
            isset($policy['refund_method']) &&
            isset($policy['restocking_fee']) &&
            $policy['restocking_fee'] >= 0;
    }

    private function validateVerificationStatus(array $store): bool
    {
        return isset($store['name']) &&
            isset($store['verified']) &&
            is_bool($store['verified']) &&
            isset($store['verification_date']) &&
            strtotime($store['verification_date']) !== false;
    }
}
