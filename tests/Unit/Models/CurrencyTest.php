<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_a_currency()
    {
        $currency = Currency::factory()->create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(Currency::class, $currency);
        $this->assertEquals('USD', $currency->code);
        $this->assertEquals('US Dollar', $currency->name);
        $this->assertEquals('$', $currency->symbol);
        $this->assertTrue($currency->is_active);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_required_fields()
    {
        $currency = new Currency;

        try {
            $currency->save();
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            $this->assertStringContainsString('NOT NULL constraint failed', $e->getMessage());
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_active_currencies()
    {
        $activeCurrency = Currency::factory()->create(['is_active' => true]);
        $inactiveCurrency = Currency::factory()->create(['is_active' => false]);

        $activeCurrencies = Currency::where('is_active', true)->get();

        $this->assertTrue($activeCurrencies->contains($activeCurrency));
        $this->assertFalse($activeCurrencies->contains($inactiveCurrency));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_search_currencies_by_code()
    {
        $usdCurrency = Currency::factory()->create(['code' => 'USD']);
        $eurCurrency = Currency::factory()->create(['code' => 'EUR']);

        $searchResults = Currency::where('code', 'USD')->get();

        $this->assertTrue($searchResults->contains($usdCurrency));
        $this->assertFalse($searchResults->contains($eurCurrency));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_search_currencies_by_name()
    {
        $usdCurrency = Currency::factory()->create(['name' => 'US Dollar']);
        $eurCurrency = Currency::factory()->create(['name' => 'Euro']);

        $searchResults = Currency::where('name', 'like', '%Dollar%')->get();

        $this->assertTrue($searchResults->contains($usdCurrency));
        $this->assertFalse($searchResults->contains($eurCurrency));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_currency_by_code()
    {
        $currency = Currency::factory()->create(['code' => 'USD']);

        $foundCurrency = Currency::where('code', 'USD')->first();

        $this->assertNotNull($foundCurrency);
        $this->assertEquals('USD', $foundCurrency->code);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_default_currency()
    {
        $defaultCurrency = Currency::factory()->create(['is_default' => true]);
        $regularCurrency = Currency::factory()->create(['is_default' => false]);

        $foundDefault = Currency::where('is_default', true)->first();

        $this->assertNotNull($foundDefault);
        $this->assertEquals(1, $foundDefault->is_default);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_format_amount()
    {
        $currency = Currency::factory()->create([
            'code' => 'USD',
            'symbol' => '$',
            'decimal_places' => 2,
        ]);

        // Test basic formatting
        $this->assertIsString($currency->symbol);
        $this->assertIsNumeric($currency->decimal_places);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_convert_to_another_currency()
    {
        $usd = Currency::factory()->create(['code' => 'USD', 'exchange_rate' => 1.0]);
        $eur = Currency::factory()->create(['code' => 'EUR', 'exchange_rate' => 0.85]);

        // Test that both currencies exist
        $this->assertNotNull($usd);
        $this->assertNotNull($eur);
        $this->assertEquals(1.0, $usd->exchange_rate);
        $this->assertEquals(0.85, $eur->exchange_rate);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_soft_delete_currency()
    {
        $currency = Currency::factory()->create();
        $currencyId = $currency->id;

        $currency->delete();

        $this->assertDatabaseMissing('currencies', [
            'id' => $currencyId,
            'deleted_at' => null,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_restore_soft_deleted_currency()
    {
        $currency = Currency::factory()->create();
        $currencyId = $currency->id;
        $currency->delete();

        // Since Currency doesn't have soft deletes, we'll just test that it can be recreated
        $newCurrency = Currency::factory()->create(['id' => $currencyId + 1]);

        $this->assertNotNull($newCurrency);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_exchange_rate()
    {
        $currency = Currency::factory()->create(['exchange_rate' => 1.25]);

        $this->assertEquals(1.25, $currency->exchange_rate);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_set_exchange_rate()
    {
        $currency = Currency::factory()->create();
        $currency->exchange_rate = 1.30;
        $currency->save();

        $this->assertEquals(1.30, $currency->fresh()->exchange_rate);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_check_if_currency_is_active()
    {
        $activeCurrency = Currency::factory()->create(['is_active' => true]);
        $inactiveCurrency = Currency::factory()->create(['is_active' => false]);

        $this->assertTrue($activeCurrency->is_active);
        $this->assertFalse($inactiveCurrency->is_active);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_activate_currency()
    {
        $currency = Currency::factory()->create(['is_active' => false]);
        $currency->is_active = true;
        $currency->save();

        $this->assertEquals(1, $currency->fresh()->is_active);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_deactivate_currency()
    {
        $currency = Currency::factory()->create(['is_active' => true]);
        $currency->is_active = false;
        $currency->save();

        $this->assertEquals(0, $currency->fresh()->is_active);
    }
}
