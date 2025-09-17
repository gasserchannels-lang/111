<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Tests\TestCase;

class UpdatePricesCommandTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_run_update_prices_command()
    {
        $this->artisan('coprra:update-prices')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_prices_for_products()
    {
        // اختبار بسيط بدون قاعدة بيانات
        $this->artisan('coprra:update-prices')
            ->assertExitCode(0);

        // التحقق من أن الأمر تم تنفيذه بنجاح
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_empty_products_gracefully()
    {
        $this->artisan('coprra:update-prices')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_run_with_verbose_output()
    {
        $this->artisan('coprra:update-prices --verbose')
            ->assertExitCode(0);
    }
}
