<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\SafeTestBase;

class TestMinimal extends SafeTestBase
{
    #[Test]
    public function test_simple(): void
    {
        $this->assertNotEmpty('test');
    }
}
