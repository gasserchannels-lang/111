<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TestDirect extends TestCase
{
    #[Test]
    public function test_simple(): void
    {
        $this->assertNotEmpty('test');
    }
}
