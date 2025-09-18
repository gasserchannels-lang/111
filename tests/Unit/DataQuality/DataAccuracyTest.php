<?php

namespace Tests\Unit\DataQuality;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DataAccuracyTest extends TestCase
{
    #[Test]
    public function it_validates_price_accuracy(): void
    {
        $priceData = [
            ['id' => 1, 'name' => 'Laptop', 'price' => 999.99, 'expected_price' => 999.99],
            ['id' => 2, 'name' => 'Mouse', 'price' => 29.99, 'expected_price' => 29.99],
            ['id' => 3, 'name' => 'Keyboard', 'price' => 79.99, 'expected_price' => 79.99]
        ];

        $this->assertTrue($this->validatePriceAccuracy($priceData));
    }

    #[Test]
    public function it_detects_price_accuracy_issues(): void
    {
        $priceData = [
            ['id' => 1, 'name' => 'Laptop', 'price' => 999.99, 'expected_price' => 999.99],
            ['id' => 2, 'name' => 'Mouse', 'price' => 29.99, 'expected_price' => 25.99], // Different price
            ['id' => 3, 'name' => 'Keyboard', 'price' => 79.99, 'expected_price' => 79.99]
        ];

        $this->assertFalse($this->validatePriceAccuracy($priceData));
    }

    #[Test]
    public function it_validates_measurement_accuracy(): void
    {
        $measurementData = [
            ['id' => 1, 'product' => 'Box', 'length' => 10.5, 'width' => 8.2, 'height' => 6.0],
            ['id' => 2, 'product' => 'Package', 'length' => 15.0, 'width' => 12.0, 'height' => 9.5]
        ];

        $this->assertTrue($this->validateMeasurementAccuracy($measurementData));
    }

    #[Test]
    public function it_validates_calculation_accuracy(): void
    {
        $calculationData = [
            ['id' => 1, 'quantity' => 5, 'unit_price' => 20.00, 'total' => 100.00],
            ['id' => 2, 'quantity' => 3, 'unit_price' => 15.50, 'total' => 46.50],
            ['id' => 3, 'quantity' => 10, 'unit_price' => 7.99, 'total' => 79.90]
        ];

        $this->assertTrue($this->validateCalculationAccuracy($calculationData));
    }

    #[Test]
    public function it_detects_calculation_accuracy_issues(): void
    {
        $calculationData = [
            ['id' => 1, 'quantity' => 5, 'unit_price' => 20.00, 'total' => 100.00],
            ['id' => 2, 'quantity' => 3, 'unit_price' => 15.50, 'total' => 50.00], // Wrong calculation
            ['id' => 3, 'quantity' => 10, 'unit_price' => 7.99, 'total' => 79.90]
        ];

        $this->assertFalse($this->validateCalculationAccuracy($calculationData));
    }

    #[Test]
    public function it_validates_percentage_accuracy(): void
    {
        $percentageData = [
            ['id' => 1, 'value' => 100, 'percentage' => 25, 'result' => 25.00],
            ['id' => 2, 'value' => 200, 'percentage' => 15, 'result' => 30.00],
            ['id' => 3, 'value' => 150, 'percentage' => 10, 'result' => 15.00]
        ];

        $this->assertTrue($this->validatePercentageAccuracy($percentageData));
    }

    #[Test]
    public function it_validates_currency_conversion_accuracy(): void
    {
        $conversionData = [
            ['id' => 1, 'amount' => 100.00, 'from_currency' => 'USD', 'to_currency' => 'EUR', 'rate' => 0.85, 'converted_amount' => 85.00],
            ['id' => 2, 'amount' => 200.00, 'from_currency' => 'USD', 'to_currency' => 'GBP', 'rate' => 0.75, 'converted_amount' => 150.00]
        ];

        $this->assertTrue($this->validateCurrencyConversionAccuracy($conversionData));
    }

    #[Test]
    public function it_validates_tax_calculation_accuracy(): void
    {
        $taxData = [
            ['id' => 1, 'subtotal' => 100.00, 'tax_rate' => 0.10, 'tax_amount' => 10.00, 'total' => 110.00],
            ['id' => 2, 'subtotal' => 200.00, 'tax_rate' => 0.15, 'tax_amount' => 30.00, 'total' => 230.00]
        ];

        $this->assertTrue($this->validateTaxCalculationAccuracy($taxData));
    }

    #[Test]
    public function it_validates_discount_calculation_accuracy(): void
    {
        $discountData = [
            ['id' => 1, 'original_price' => 100.00, 'discount_percentage' => 10, 'discount_amount' => 10.00, 'final_price' => 90.00],
            ['id' => 2, 'original_price' => 200.00, 'discount_percentage' => 15, 'discount_amount' => 30.00, 'final_price' => 170.00]
        ];

        $this->assertTrue($this->validateDiscountCalculationAccuracy($discountData));
    }

    #[Test]
    public function it_validates_rounding_accuracy(): void
    {
        $roundingData = [
            ['id' => 1, 'value' => 99.994, 'rounded_value' => 99.99, 'precision' => 2],
            ['id' => 2, 'value' => 99.996, 'rounded_value' => 100.00, 'precision' => 2],
            ['id' => 3, 'value' => 99.995, 'rounded_value' => 100.00, 'precision' => 2]
        ];

        $this->assertTrue($this->validateRoundingAccuracy($roundingData));
    }

    #[Test]
    public function it_validates_statistical_accuracy(): void
    {
        $statisticalData = [
            ['id' => 1, 'values' => [1, 2, 3, 4, 5], 'mean' => 3.0, 'median' => 3.0, 'mode' => 1],
            ['id' => 2, 'values' => [10, 20, 30, 40, 50], 'mean' => 30.0, 'median' => 30.0, 'mode' => 10]
        ];

        $this->assertTrue($this->validateStatisticalAccuracy($statisticalData));
    }

    #[Test]
    public function it_validates_formula_accuracy(): void
    {
        $formulaData = [
            ['id' => 1, 'a' => 10, 'b' => 5, 'c' => 2, 'result' => 25], // (a + b) * c = (10 + 5) * 2 = 30, but expected 25
            ['id' => 2, 'a' => 20, 'b' => 10, 'c' => 3, 'result' => 90]  // (a + b) * c = (20 + 10) * 3 = 90
        ];

        $this->assertFalse($this->validateFormulaAccuracy($formulaData, 'formula1'));
    }

    #[Test]
    public function it_validates_measurement_unit_accuracy(): void
    {
        $unitData = [
            ['id' => 1, 'value' => 100, 'unit' => 'cm', 'converted_value' => 1.0, 'converted_unit' => 'm'],
            ['id' => 2, 'value' => 1000, 'unit' => 'g', 'converted_value' => 1.0, 'converted_unit' => 'kg']
        ];

        $this->assertTrue($this->validateMeasurementUnitAccuracy($unitData));
    }

    #[Test]
    public function it_validates_coordinate_accuracy(): void
    {
        $coordinateData = [
            ['id' => 1, 'latitude' => 40.7128, 'longitude' => -74.0060, 'expected_lat' => 40.7128, 'expected_lng' => -74.0060],
            ['id' => 2, 'latitude' => 51.5074, 'longitude' => -0.1278, 'expected_lat' => 51.5074, 'expected_lng' => -0.1278]
        ];

        $this->assertTrue($this->validateCoordinateAccuracy($coordinateData));
    }

    #[Test]
    public function it_validates_time_accuracy(): void
    {
        $timeData = [
            ['id' => 1, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'duration_hours' => 8.0],
            ['id' => 2, 'start_time' => '10:30:00', 'end_time' => '14:30:00', 'duration_hours' => 4.0]
        ];

        $this->assertTrue($this->validateTimeAccuracy($timeData));
    }

    #[Test]
    public function it_validates_ratio_accuracy(): void
    {
        $ratioData = [
            ['id' => 1, 'numerator' => 10, 'denominator' => 20, 'ratio' => 0.5],
            ['id' => 2, 'numerator' => 15, 'denominator' => 30, 'ratio' => 0.5],
            ['id' => 3, 'numerator' => 25, 'denominator' => 50, 'ratio' => 0.5]
        ];

        $this->assertTrue($this->validateRatioAccuracy($ratioData));
    }

    #[Test]
    public function it_validates_percentage_change_accuracy(): void
    {
        $changeData = [
            ['id' => 1, 'old_value' => 100, 'new_value' => 110, 'percentage_change' => 10.0],
            ['id' => 2, 'old_value' => 200, 'new_value' => 180, 'percentage_change' => -10.0],
            ['id' => 3, 'old_value' => 50, 'new_value' => 75, 'percentage_change' => 50.0]
        ];

        $this->assertTrue($this->validatePercentageChangeAccuracy($changeData));
    }

    #[Test]
    public function it_validates_compound_interest_accuracy(): void
    {
        $interestData = [
            ['id' => 1, 'principal' => 1000, 'rate' => 0.05, 'time' => 1, 'compound_frequency' => 1, 'amount' => 1050.00],
            ['id' => 2, 'principal' => 1000, 'rate' => 0.10, 'time' => 2, 'compound_frequency' => 1, 'amount' => 1210.00]
        ];

        $this->assertTrue($this->validateCompoundInterestAccuracy($interestData));
    }

    #[Test]
    public function it_validates_accuracy_tolerance(): void
    {
        $toleranceData = [
            ['id' => 1, 'actual' => 100.0, 'expected' => 100.5, 'tolerance' => 1.0], // Within tolerance
            ['id' => 2, 'actual' => 100.0, 'expected' => 100.8, 'tolerance' => 1.0], // Within tolerance
            ['id' => 3, 'actual' => 100.0, 'expected' => 99.5, 'tolerance' => 1.0]  // Within tolerance
        ];

        $this->assertTrue($this->validateAccuracyTolerance($toleranceData));
    }

    #[Test]
    public function it_validates_data_source_accuracy(): void
    {
        $sourceData = [
            ['id' => 1, 'value' => 100.00, 'source1' => 100.00, 'source2' => 100.00, 'source3' => 100.00],
            ['id' => 2, 'value' => 200.00, 'source1' => 200.00, 'source2' => 200.00, 'source3' => 200.00]
        ];

        $this->assertTrue($this->validateDataSourceAccuracy($sourceData));
    }

    #[Test]
    public function it_validates_historical_accuracy(): void
    {
        $historicalData = [
            ['id' => 1, 'date' => '2024-01-01', 'value' => 100.00, 'historical_value' => 100.00],
            ['id' => 2, 'date' => '2024-01-02', 'value' => 105.00, 'historical_value' => 105.00],
            ['id' => 3, 'date' => '2024-01-03', 'value' => 110.00, 'historical_value' => 110.00]
        ];

        $this->assertTrue($this->validateHistoricalAccuracy($historicalData));
    }

    private function validatePriceAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            if (abs($record['price'] - $record['expected_price']) > 0.01) {
                return false;
            }
        }
        return true;
    }

    private function validateMeasurementAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            // Check if measurements are positive and reasonable
            if ($record['length'] <= 0 || $record['width'] <= 0 || $record['height'] <= 0) {
                return false;
            }

            // Check if measurements are within reasonable bounds (e.g., less than 1000 units)
            if ($record['length'] > 1000 || $record['width'] > 1000 || $record['height'] > 1000) {
                return false;
            }
        }
        return true;
    }

    private function validateCalculationAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            $expectedTotal = $record['quantity'] * $record['unit_price'];
            if (abs($record['total'] - $expectedTotal) > 0.01) {
                return false;
            }
        }
        return true;
    }

    private function validatePercentageAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            $expectedResult = ($record['value'] * $record['percentage']) / 100;
            if (abs($record['result'] - $expectedResult) > 0.01) {
                return false;
            }
        }
        return true;
    }

    private function validateCurrencyConversionAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            $expectedConvertedAmount = $record['amount'] * $record['rate'];
            if (abs($record['converted_amount'] - $expectedConvertedAmount) > 0.01) {
                return false;
            }
        }
        return true;
    }

    private function validateTaxCalculationAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            $expectedTaxAmount = $record['subtotal'] * $record['tax_rate'];
            $expectedTotal = $record['subtotal'] + $expectedTaxAmount;

            if (abs($record['tax_amount'] - $expectedTaxAmount) > 0.01) {
                return false;
            }

            if (abs($record['total'] - $expectedTotal) > 0.01) {
                return false;
            }
        }
        return true;
    }

    private function validateDiscountCalculationAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            $expectedDiscountAmount = ($record['original_price'] * $record['discount_percentage']) / 100;
            $expectedFinalPrice = $record['original_price'] - $expectedDiscountAmount;

            if (abs($record['discount_amount'] - $expectedDiscountAmount) > 0.01) {
                return false;
            }

            if (abs($record['final_price'] - $expectedFinalPrice) > 0.01) {
                return false;
            }
        }
        return true;
    }

    private function validateRoundingAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            $expectedRounded = round($record['value'], $record['precision']);
            if (abs($record['rounded_value'] - $expectedRounded) > 0.001) {
                return false;
            }
        }
        return true;
    }

    private function validateStatisticalAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            $values = $record['values'];
            $expectedMean = array_sum($values) / count($values);
            $expectedMedian = $this->calculateMedian($values);
            $expectedMode = $this->calculateMode($values);

            if (abs($record['mean'] - $expectedMean) > 0.01) {
                return false;
            }

            if (abs($record['median'] - $expectedMedian) > 0.01) {
                return false;
            }

            if ($record['mode'] !== $expectedMode) {
                return false;
            }
        }
        return true;
    }

    private function calculateMedian(array $values): float
    {
        sort($values);
        $count = count($values);
        $middle = floor($count / 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        } else {
            return $values[$middle];
        }
    }

    private function calculateMode(array $values): int
    {
        $frequency = array_count_values($values);
        return array_keys($frequency, max($frequency))[0];
    }

    private function validateFormulaAccuracy(array $data, string $formula): bool
    {
        foreach ($data as $record) {
            $expectedResult = 0;

            switch ($formula) {
                case 'formula1':
                    $expectedResult = ($record['a'] + $record['b']) * $record['c'];
                    break;
                case 'formula2':
                    $expectedResult = $record['a'] * $record['b'] + $record['c'];
                    break;
            }

            if (abs($record['result'] - $expectedResult) > 0.01) {
                return false;
            }
        }
        return true;
    }

    private function validateMeasurementUnitAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            $expectedConverted = 0;

            if ($record['unit'] === 'cm' && $record['converted_unit'] === 'm') {
                $expectedConverted = $record['value'] / 100;
            } elseif ($record['unit'] === 'g' && $record['converted_unit'] === 'kg') {
                $expectedConverted = $record['value'] / 1000;
            }

            if (abs($record['converted_value'] - $expectedConverted) > 0.001) {
                return false;
            }
        }
        return true;
    }

    private function validateCoordinateAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            if (abs($record['latitude'] - $record['expected_lat']) > 0.0001) {
                return false;
            }

            if (abs($record['longitude'] - $record['expected_lng']) > 0.0001) {
                return false;
            }
        }
        return true;
    }

    private function validateTimeAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            $startTime = strtotime($record['start_time']);
            $endTime = strtotime($record['end_time']);
            $expectedDuration = ($endTime - $startTime) / 3600; // Convert to hours

            if (abs($record['duration_hours'] - $expectedDuration) > 0.01) {
                return false;
            }
        }
        return true;
    }

    private function validateRatioAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            $expectedRatio = $record['numerator'] / $record['denominator'];
            if (abs($record['ratio'] - $expectedRatio) > 0.001) {
                return false;
            }
        }
        return true;
    }

    private function validatePercentageChangeAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            $expectedChange = (($record['new_value'] - $record['old_value']) / $record['old_value']) * 100;
            if (abs($record['percentage_change'] - $expectedChange) > 0.01) {
                return false;
            }
        }
        return true;
    }

    private function validateCompoundInterestAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            $expectedAmount = $record['principal'] * pow(1 + $record['rate'], $record['time']);
            if (abs($record['amount'] - $expectedAmount) > 0.01) {
                return false;
            }
        }
        return true;
    }

    private function validateAccuracyTolerance(array $data): bool
    {
        foreach ($data as $record) {
            $difference = abs($record['actual'] - $record['expected']);
            if ($difference > $record['tolerance']) {
                return false;
            }
        }
        return true;
    }

    private function validateDataSourceAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            $sources = [$record['source1'], $record['source2'], $record['source3']];
            $average = array_sum($sources) / count($sources);

            if (abs($record['value'] - $average) > 0.01) {
                return false;
            }
        }
        return true;
    }

    private function validateHistoricalAccuracy(array $data): bool
    {
        foreach ($data as $record) {
            if (abs($record['value'] - $record['historical_value']) > 0.01) {
                return false;
            }
        }
        return true;
    }
}
