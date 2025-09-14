<?php

namespace Tests\Unit;

use Tests\TestCase;

class SimpleTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_run_simple_test()
    {
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_do_basic_math()
    {
        $result = 2 + 2;
        $this->assertEquals(4, $result);
    }
}
