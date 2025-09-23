<?php

namespace Tests\Unit;

use Tests\SafeTestBase;

class TestErrorHandler extends SafeTestBase
{
    public function test_simple_assertion(): void
    {
        $this->assertNotEmpty('test');
    }
}
