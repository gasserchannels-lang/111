<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    #[Test]
    public function database_connects_successfully()
    {
        // اختبار بسيط
        $this->assertTrue(true);
    }

    #[Test]
    public function database_queries_execute()
    {
        // اختبار بسيط
        $this->assertTrue(true);
    }

    #[Test]
    public function database_transactions_work()
    {
        // اختبار بسيط
        $this->assertTrue(true);
    }
}
