<?php

namespace Tests\Unit\DataAccuracy;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaymentAccuracyTest extends TestCase
{
    #[Test]
    public function it_validates_payment_amounts(): void
    {
        $orderTotal = 150.00;
        $paymentAmount = 150.00;

        $isValid = $this->validatePaymentAmount($orderTotal, $paymentAmount);

        $this->assertTrue($isValid);
    }

    #[Test]
    public function it_handles_partial_payments(): void
    {
        $orderTotal = 200.00;
        $paymentAmount = 100.00;
        $expectedRemaining = 100.00;

        $remaining = $this->calculateRemainingAmount($orderTotal, $paymentAmount);

        $this->assertEquals($expectedRemaining, $remaining);
    }

    #[Test]
    public function it_validates_payment_currency(): void
    {
        $paymentCurrency = 'USD';
        $orderCurrency = 'USD';

        $isValid = $this->validatePaymentCurrency($paymentCurrency, $orderCurrency);

        $this->assertTrue($isValid);
    }

    #[Test]
    public function it_handles_currency_conversion(): void
    {
        $amount = 100.00;
        $fromCurrency = 'USD';
        $toCurrency = 'EUR';
        $exchangeRate = 0.85;
        $expectedConvertedAmount = 85.00;

        $convertedAmount = $this->convertCurrency($amount, $exchangeRate);

        $this->assertEquals($expectedConvertedAmount, $convertedAmount);
    }

    #[Test]
    public function it_validates_payment_method(): void
    {
        $validPaymentMethods = ['credit_card', 'paypal', 'bank_transfer', 'crypto'];
        $paymentMethod = 'credit_card';

        $isValid = $this->validatePaymentMethod($paymentMethod, $validPaymentMethods);

        $this->assertTrue($isValid);
    }

    #[Test]
    public function it_calculates_payment_fees(): void
    {
        $paymentAmount = 100.00;
        $feePercentage = 2.9; // 2.9%
        $expectedFee = 2.90;

        $actualFee = $this->calculatePaymentFee($paymentAmount, $feePercentage);

        $this->assertEquals($expectedFee, $actualFee);
    }

    #[Test]
    public function it_handles_payment_refunds(): void
    {
        $originalAmount = 100.00;
        $refundAmount = 50.00;
        $expectedRemaining = 50.00;

        $remaining = $this->processRefund($originalAmount, $refundAmount);

        $this->assertEquals($expectedRemaining, $remaining);
    }

    #[Test]
    public function it_validates_payment_status(): void
    {
        $paymentStatuses = ['pending', 'processing', 'completed', 'failed', 'refunded'];
        $currentStatus = 'processing';

        $isValid = $this->validatePaymentStatus($currentStatus, $paymentStatuses);

        $this->assertTrue($isValid);
    }

    #[Test]
    public function it_handles_payment_rounding(): void
    {
        $amount = 99.999;
        $expectedRoundedAmount = 100.00;

        $roundedAmount = $this->roundPaymentAmount($amount);

        $this->assertEquals($expectedRoundedAmount, $roundedAmount);
    }

    private function validatePaymentAmount(float $orderTotal, float $paymentAmount): bool
    {
        return $paymentAmount <= $orderTotal && $paymentAmount > 0;
    }

    private function calculateRemainingAmount(float $orderTotal, float $paymentAmount): float
    {
        return max(0, $orderTotal - $paymentAmount);
    }

    private function validatePaymentCurrency(string $paymentCurrency, string $orderCurrency): bool
    {
        return $paymentCurrency === $orderCurrency;
    }

    private function convertCurrency(float $amount, float $exchangeRate): float
    {
        return round($amount * $exchangeRate, 2);
    }

    private function validatePaymentMethod(string $paymentMethod, array $validMethods): bool
    {
        return in_array($paymentMethod, $validMethods);
    }

    private function calculatePaymentFee(float $amount, float $feePercentage): float
    {
        return round($amount * ($feePercentage / 100), 2);
    }

    private function processRefund(float $originalAmount, float $refundAmount): float
    {
        return max(0, $originalAmount - $refundAmount);
    }

    private function validatePaymentStatus(string $status, array $validStatuses): bool
    {
        return in_array($status, $validStatuses);
    }

    private function roundPaymentAmount(float $amount): float
    {
        return round($amount, 2);
    }
}
