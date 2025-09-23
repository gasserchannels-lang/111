<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TestPure extends TestCase
{
    #[Test]
    public function test_simple(): void
    {
        $this->assertNotEmpty('test');
    }
}
