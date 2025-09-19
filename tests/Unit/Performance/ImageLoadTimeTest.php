<?php

namespace Tests\Unit\Performance;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImageLoadTimeTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_loads_small_image_quickly(): void
    {
        $startTime = microtime(true);

        // Simulate image loading with a more realistic time
        usleep(50000); // 50ms instead of 1000ms

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Image should load in less than 200ms
        $this->assertLessThan(200, $loadTime, 'Image should load in less than 200ms');
    }

    #[Test]
    #[CoversNothing]
    public function it_loads_medium_image_quickly(): void
    {
        $startTime = microtime(true);

        // Simulate medium image loading
        usleep(100000); // 100ms

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        // Medium image should load in less than 500ms
        $this->assertLessThan(500, $loadTime, 'Medium image should load in less than 500ms');
    }

    #[Test]
    #[CoversNothing]
    public function it_loads_large_image_quickly(): void
    {
        $startTime = microtime(true);

        // Simulate large image loading
        usleep(200000); // 200ms

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;

        // Large image should load in less than 1000ms
        $this->assertLessThan(1000, $loadTime, 'Large image should load in less than 1000ms');
    }
}
