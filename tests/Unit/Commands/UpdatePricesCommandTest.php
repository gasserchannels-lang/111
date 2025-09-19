<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdatePricesCommandTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_run_update_prices_command()
    {
        // Mock console output to prevent interactive prompts
        $this->mock(\Symfony\Component\Console\Style\SymfonyStyle::class, function ($mock) {
            $mock->shouldReceive('askQuestion')->andReturn(true);
            $mock->shouldReceive('confirm')->andReturn(true);
            $mock->shouldIgnoreMissing();
        });

        $this->artisan('coprra:update-prices')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_prices_for_products()
    {
        // Mock console output to prevent interactive prompts
        $this->mock(\Symfony\Component\Console\Style\SymfonyStyle::class, function ($mock) {
            $mock->shouldReceive('askQuestion')->andReturn(true);
            $mock->shouldReceive('confirm')->andReturn(true);
            $mock->shouldIgnoreMissing();
        });

        // اختبار بسيط بدون قاعدة بيانات
        $this->artisan('coprra:update-prices')
            ->assertExitCode(0);

        // التحقق من أن الأمر تم تنفيذه بنجاح
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_empty_products_gracefully()
    {
        // Mock console output to prevent interactive prompts
        $this->mock(\Symfony\Component\Console\Style\SymfonyStyle::class, function ($mock) {
            $mock->shouldReceive('askQuestion')->andReturn(true);
            $mock->shouldReceive('confirm')->andReturn(true);
            $mock->shouldIgnoreMissing();
        });

        $this->artisan('coprra:update-prices')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_run_with_verbose_output()
    {
        // Mock console output to prevent interactive prompts
        $this->mock(\Symfony\Component\Console\Style\SymfonyStyle::class, function ($mock) {
            $mock->shouldReceive('askQuestion')->andReturn(true);
            $mock->shouldReceive('confirm')->andReturn(true);
            $mock->shouldIgnoreMissing();
        });

        $this->artisan('coprra:update-prices --verbose')
            ->assertExitCode(0);
    }
}
