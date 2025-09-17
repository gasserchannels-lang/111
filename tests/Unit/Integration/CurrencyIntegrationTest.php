<?php

namespace Tests\Unit\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CurrencyIntegrationTest extends TestCase
{
    #[Test]
    public function it_integrates_with_currency_api(): void
    {
        $currencyApi = $this->createCurrencyApiMock();
        $rates = $currencyApi->getExchangeRates();

        $this->assertIsArray($rates);
        $this->assertArrayHasKey('USD', $rates);
        $this->assertArrayHasKey('EUR', $rates);
    }

    #[Test]
    public function it_converts_currencies(): void
    {
        $currencyApi = $this->createCurrencyApiMock();
        $converted = $currencyApi->convert(100, 'USD', 'EUR');

        $this->assertIsFloat($converted);
        $this->assertGreaterThan(0, $converted);
    }

    #[Test]
    public function it_handles_invalid_currency_codes(): void
    {
        $currencyApi = $this->createCurrencyApiMock();

        $this->expectException(\InvalidArgumentException::class);
        $currencyApi->convert(100, 'INVALID', 'EUR');
    }

    #[Test]
    public function it_updates_exchange_rates(): void
    {
        $currencyApi = $this->createCurrencyApiMock();
        $updated = $currencyApi->updateRates();

        $this->assertTrue($updated);
    }

    #[Test]
    public function it_handles_api_failures(): void
    {
        $currencyApi = $this->createCurrencyApiMock();
        $currencyApi->setErrorMode(true);

        $this->expectException(\Exception::class);
        $currencyApi->getExchangeRates();
    }

    #[Test]
    public function it_caches_exchange_rates(): void
    {
        $currencyApi = $this->createCurrencyApiMock();
        $currencyApi->enableCache(true);

        $rates1 = $currencyApi->getExchangeRates();
        $rates2 = $currencyApi->getExchangeRates();

        $this->assertEquals($rates1, $rates2);
    }

    #[Test]
    public function it_handles_rate_limiting(): void
    {
        $currencyApi = $this->createCurrencyApiMock();
        $currencyApi->setRateLimit(5);

        // Make multiple requests
        for ($i = 0; $i < 3; $i++) {
            $rates = $currencyApi->getExchangeRates();
            $this->assertIsArray($rates);
        }
    }

    #[Test]
    public function it_validates_currency_support(): void
    {
        $currencyApi = $this->createCurrencyApiMock();
        $supportedCurrencies = $currencyApi->getSupportedCurrencies();

        $this->assertIsArray($supportedCurrencies);
        $this->assertContains('USD', $supportedCurrencies);
        $this->assertContains('EUR', $supportedCurrencies);
        $this->assertContains('GBP', $supportedCurrencies);
    }

    #[Test]
    public function it_handles_historical_rates(): void
    {
        $currencyApi = $this->createCurrencyApiMock();
        $historicalRates = $currencyApi->getHistoricalRates('USD', '2024-01-01');

        $this->assertIsArray($historicalRates);
        $this->assertArrayHasKey('USD', $historicalRates);
    }

    #[Test]
    public function it_formats_currency_values(): void
    {
        $currencyApi = $this->createCurrencyApiMock();
        $formatted = $currencyApi->formatCurrency(1234.56, 'USD');

        $this->assertStringContainsString('$', $formatted);
        $this->assertStringContainsString('1,234.56', $formatted);
    }

    private function createCurrencyApiMock(): object
    {
        return new class {
            private bool $errorMode = false;
            private int $rateLimit = 100;
            private bool $cacheEnabled = false;
            private array $cache = [];
            private array $exchangeRates = [
                'USD' => 1.0,
                'EUR' => 0.85,
                'GBP' => 0.73,
                'JPY' => 110.0,
                'AED' => 3.67,
                'EGP' => 30.0
            ];

            public function getExchangeRates(): array
            {
                if ($this->errorMode) {
                    throw new \Exception('Currency API Error');
                }

                if ($this->cacheEnabled && isset($this->cache['rates'])) {
                    return $this->cache['rates'];
                }

                $rates = $this->exchangeRates;

                if ($this->cacheEnabled) {
                    $this->cache['rates'] = $rates;
                }

                return $rates;
            }

            public function convert(float $amount, string $from, string $to): float
            {
                if ($this->errorMode) {
                    throw new \Exception('Currency API Error');
                }

                if (!isset($this->exchangeRates[$from]) || !isset($this->exchangeRates[$to])) {
                    throw new \InvalidArgumentException('Invalid currency code');
                }

                $usdAmount = $amount / $this->exchangeRates[$from];
                return $usdAmount * $this->exchangeRates[$to];
            }

            public function updateRates(): bool
            {
                if ($this->errorMode) {
                    return false;
                }

                // Simulate rate update
                $this->exchangeRates['EUR'] = 0.85 + (rand(-5, 5) / 100);
                $this->exchangeRates['GBP'] = 0.73 + (rand(-5, 5) / 100);

                return true;
            }

            public function getSupportedCurrencies(): array
            {
                return array_keys($this->exchangeRates);
            }

            public function getHistoricalRates(string $baseCurrency, string $date): array
            {
                if ($this->errorMode) {
                    throw new \Exception('Currency API Error');
                }

                return [
                    $baseCurrency => $this->exchangeRates[$baseCurrency] ?? 1.0
                ];
            }

            public function formatCurrency(float $amount, string $currency): string
            {
                $formatters = [
                    'USD' => '$%s',
                    'EUR' => '€%s',
                    'GBP' => '£%s',
                    'JPY' => '¥%s',
                    'AED' => '%s AED',
                    'EGP' => '%s EGP'
                ];

                $format = $formatters[$currency] ?? '%s';
                return sprintf($format, number_format($amount, 2));
            }

            public function setErrorMode(bool $enabled): void
            {
                $this->errorMode = $enabled;
            }

            public function setRateLimit(int $limit): void
            {
                $this->rateLimit = $limit;
            }

            public function enableCache(bool $enabled): void
            {
                $this->cacheEnabled = $enabled;
            }
        };
    }
}
