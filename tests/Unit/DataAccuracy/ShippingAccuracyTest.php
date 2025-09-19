<?php

namespace Tests\Unit\DataAccuracy;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShippingAccuracyTest extends TestCase
{
    #[Test]
    public function it_calculates_shipping_costs_correctly(): void
    {
        $weight = 2.5; // kg
        $distance = 100; // km
        $shippingRate = 0.5; // per kg per km
        $expectedCost = 125.00; // 2.5 * 100 * 0.5

        $actualCost = $this->calculateShippingCost($weight, $distance, $shippingRate);

        $this->assertEquals($expectedCost, $actualCost);
    }

    #[Test]
    public function it_handles_free_shipping_threshold(): void
    {
        $orderTotal = 150.00;
        $freeShippingThreshold = 100.00;

        $isFreeShipping = $this->isEligibleForFreeShipping($orderTotal, $freeShippingThreshold);

        $this->assertTrue($isFreeShipping);
    }

    #[Test]
    public function it_calculates_express_shipping_cost(): void
    {
        $baseCost = 50.00;
        $expressMultiplier = 2.0;
        $expectedCost = 100.00;

        $actualCost = $this->calculateExpressShippingCost($baseCost, $expressMultiplier);

        $this->assertEquals($expectedCost, $actualCost);
    }

    #[Test]
    public function it_validates_shipping_address(): void
    {
        $address = [
            'street' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'zip' => '10001',
            'country' => 'USA',
        ];

        $isValid = $this->validateShippingAddress($address);

        $this->assertTrue($isValid);
    }

    #[Test]
    public function it_calculates_delivery_time(): void
    {
        $distance = 200; // km
        $averageSpeed = 50; // km/h
        $expectedHours = 4.0;

        $actualHours = $this->calculateDeliveryTime($distance, $averageSpeed);

        $this->assertEquals($expectedHours, $actualHours);
    }

    #[Test]
    public function it_handles_oversized_items(): void
    {
        $dimensions = ['length' => 150, 'width' => 100, 'height' => 80]; // cm
        $maxDimensions = ['length' => 120, 'width' => 80, 'height' => 60]; // cm

        $isOversized = $this->isOversizedItem($dimensions, $maxDimensions);

        $this->assertTrue($isOversized);
    }

    #[Test]
    public function it_calculates_insurance_cost(): void
    {
        $itemValue = 1000.00;
        $insuranceRate = 0.01; // 1%
        $expectedInsurance = 10.00;

        $actualInsurance = $this->calculateInsuranceCost($itemValue, $insuranceRate);

        $this->assertEquals($expectedInsurance, $actualInsurance);
    }

    #[Test]
    public function it_handles_international_shipping(): void
    {
        $domesticCost = 50.00;
        $internationalMultiplier = 3.0;
        $customsFee = 25.00;
        $expectedCost = 175.00; // (50 * 3) + 25

        $actualCost = $this->calculateInternationalShippingCost($domesticCost, $internationalMultiplier, $customsFee);

        $this->assertEquals($expectedCost, $actualCost);
    }

    #[Test]
    public function it_validates_shipping_zones(): void
    {
        $shippingZones = [
            'zone_1' => ['USA', 'Canada'],
            'zone_2' => ['UK', 'Germany', 'France'],
            'zone_3' => ['Japan', 'Australia'],
        ];

        $country = 'USA';
        $expectedZone = 'zone_1';

        $actualZone = $this->getShippingZone($country, $shippingZones);

        $this->assertEquals($expectedZone, $actualZone);
    }

    private function calculateShippingCost(float $weight, float $distance, float $rate): float
    {
        return round($weight * $distance * $rate, 2);
    }

    private function isEligibleForFreeShipping(float $orderTotal, float $threshold): bool
    {
        return $orderTotal >= $threshold;
    }

    private function calculateExpressShippingCost(float $baseCost, float $multiplier): float
    {
        return round($baseCost * $multiplier, 2);
    }

    private function validateShippingAddress(array $address): bool
    {
        $requiredFields = ['street', 'city', 'state', 'zip', 'country'];

        foreach ($requiredFields as $field) {
            if (empty($address[$field])) {
                return false;
            }
        }

        return true;
    }

    private function calculateDeliveryTime(float $distance, float $averageSpeed): float
    {
        return round($distance / $averageSpeed, 1);
    }

    private function isOversizedItem(array $dimensions, array $maxDimensions): bool
    {
        return $dimensions['length'] > $maxDimensions['length'] ||
            $dimensions['width'] > $maxDimensions['width'] ||
            $dimensions['height'] > $maxDimensions['height'];
    }

    private function calculateInsuranceCost(float $itemValue, float $rate): float
    {
        return round($itemValue * $rate, 2);
    }

    private function calculateInternationalShippingCost(float $domesticCost, float $multiplier, float $customsFee): float
    {
        return round(($domesticCost * $multiplier) + $customsFee, 2);
    }

    private function getShippingZone(string $country, array $zones): ?string
    {
        foreach ($zones as $zone => $countries) {
            if (in_array($country, $countries)) {
                return $zone;
            }
        }

        return null;
    }
}
