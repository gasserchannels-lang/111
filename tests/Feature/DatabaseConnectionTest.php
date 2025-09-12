<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function database_connection_works()
    {
        $this->assertTrue(DB::connection()->getPdo() !== null);
    }

    /** @test */
    public function can_run_database_queries()
    {
        $result = DB::select('SELECT 1 as test');
        $this->assertEquals(1, $result[0]->test);
    }

    /** @test */
    public function database_migrations_work()
    {
        $this->artisan('migrate:status');
        $this->assertTrue(true);
    }

    /** @test */
    public function can_create_and_read_records()
    {
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
