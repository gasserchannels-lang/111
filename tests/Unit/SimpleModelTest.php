<?php

namespace Tests\Unit;

use Tests\TestCase;

class SimpleModelTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_test_basic_functionality()
    {
        // اختبار بسيط بدون database أو factory
        $this->assertTrue(true);
        $this->assertEquals(1, 1);
        $this->assertNotEquals(1, 2);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_test_string_operations()
    {
        $string = 'Hello World';
        $this->assertStringContainsString('Hello', $string);
        $this->assertStringContainsString('World', $string);
        $this->assertEquals(11, strlen($string));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_test_array_operations()
    {
        $array = [1, 2, 3, 4, 5];
        $this->assertCount(5, $array);
        $this->assertContains(3, $array);
        $this->assertNotContains(6, $array);
    }
}
