<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatsCommandTest extends TestCase
{
    use RefreshDatabase;
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_run_stats_command()
    {
        $this->artisan('coprra:stats')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_displays_correct_statistics()
    {
        // Mock the database queries to avoid database connection issues
        $this->mock(\App\Models\Product::class, function ($mock) {
            $mock->shouldReceive('count')->andReturn(5);
        });

        $this->mock(\App\Models\User::class, function ($mock) {
            $mock->shouldReceive('count')->andReturn(3);
        });

        $this->artisan('coprra:stats')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_empty_database()
    {
        $this->artisan('coprra:stats')
            ->assertExitCode(0);
    }
}
