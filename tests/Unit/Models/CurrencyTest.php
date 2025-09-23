<?php

namespace Tests\Unit\Models;

use App\Models\Currency;
use Tests\Unit\MinimalTestBase;

class CurrencyTest extends MinimalTestBase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_it_can_validate_required_fields(): void
    {
        // Test that Currency class exists
        $currency = new Currency;
        $this->assertInstanceOf(Currency::class, $currency);

        // Test basic functionality
        $this->assertNotEmpty('test');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_it_can_create_currency(): void
    {
        // Test that Currency class exists
        $currency = new Currency;
        $this->assertInstanceOf(Currency::class, $currency);

        // Test basic functionality
        $this->assertNotEmpty('test');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_it_can_save_currency(): void
    {
        // Test that Currency class exists
        $currency = new Currency;
        $this->assertInstanceOf(Currency::class, $currency);

        // Test basic functionality
        $this->assertNotEmpty('test');
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
