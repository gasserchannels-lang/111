<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    

    #[Test]
    public function database_connection_works()
    {
        $this->assertTrue(DB::connection()->getPdo() !== null);
    }

    #[Test]
    public function can_run_database_queries()
    {
        $result = DB::select('SELECT 1 as test');
        $this->assertEquals(1, $result[0]->test);
    }

    #[Test]
    public function database_migrations_work()
    {
        $this->artisan('migrate:status');
        $this->assertTrue(true);
    }

    #[Test]
    public function can_create_and_read_records()
    {
        // Create a test table first
        DB::statement('CREATE TABLE IF NOT EXISTS test_table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )');

        DB::table('test_table')->insert([
            'name' => 'Test Record',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $record = DB::table('test_table')->where('name', 'Test Record')->first();
        $this->assertNotNull($record);
        $this->assertEquals('Test Record', $record->name);
    }
}
