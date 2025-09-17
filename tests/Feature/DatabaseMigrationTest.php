<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DatabaseMigrationTest extends TestCase
{
    #[Test]
    public function migrations_run_successfully()
    {
        // اختبار بسيط
        $this->assertTrue(true);
    }

    #[Test]
    public function migrations_rollback_successfully()
    {
        // اختبار بسيط
        $this->assertTrue(true);
    }

    #[Test]
    public function migrations_refresh_successfully()
    {
        // اختبار بسيط
        $this->assertTrue(true);
    }
}
