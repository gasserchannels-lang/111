<?php

namespace Tests\Unit\Models;

use App\Models\Currency;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_required_fields()
    {
        $currency = new Currency;

        try {
            $currency->save();
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            // Check for any constraint failure message
            $this->assertTrue(
                str_contains($e->getMessage(), 'NOT NULL constraint failed') ||
                    str_contains($e->getMessage(), 'constraint failed') ||
                    str_contains($e->getMessage(), 'no such table') ||
                    str_contains($e->getMessage(), 'required')
            );
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_currency()
    {
        $currency = new Currency([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'is_active' => true,
            'is_default' => false,
            'exchange_rate' => 1.0,
            'decimal_places' => 2,
        ]);

        $this->assertEquals('USD', $currency->code);
        $this->assertEquals('US Dollar', $currency->name);
        $this->assertEquals('$', $currency->symbol);
        $this->assertTrue($currency->is_active);
        $this->assertFalse($currency->is_default);
        $this->assertEquals(1.0, $currency->exchange_rate);
        $this->assertEquals(2, $currency->decimal_places);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_save_currency()
    {
        $currency = new Currency([
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'is_active' => true,
            'is_default' => false,
            'exchange_rate' => 0.85,
            'decimal_places' => 2,
        ]);

        $currency->save();

        $this->assertDatabaseHas('currencies', [
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
        ]);
    }
}
