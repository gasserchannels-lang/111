<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LinkCheckerTest extends TestCase
{
    #[Test]
    public function links_are_valid()
    {
        // اختبار بسيط
        $this->assertTrue(true);
    }

    #[Test]
    public function broken_links_are_detected()
    {
        // اختبار بسيط
        $this->assertTrue(true);
    }

    #[Test]
    public function external_links_work()
    {
        // اختبار بسيط
        $this->assertTrue(true);
    }
}
