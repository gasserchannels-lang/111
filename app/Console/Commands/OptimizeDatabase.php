<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class OptimizeDatabase extends Command
{
    protected $signature = 'db:optimize';

    protected $description = 'Optimize database performance by adding indexes and analyzing tables';

    public function handle()
    {
        $this->info('Starting database optimization...');

        // Add missing indexes
        $this->addIndexes();

        // Analyze tables
        $this->analyzeTables();

        // Optimize tables
        $this->optimizeTables();

        // Clear query cache
        Artisan::call('cache:clear');

        $this->info('Database optimization completed!');
    }

    private function addIndexes()
    {
        $this->info('Adding database indexes...');

        $indexes = [
            'ALTER TABLE products ADD INDEX idx_category_price (category_id, price)',
            'ALTER TABLE products ADD INDEX idx_brand_rating (brand_id, rating)',
            'ALTER TABLE products ADD INDEX idx_created_at (created_at)',
            'ALTER TABLE orders ADD INDEX idx_user_status (user_id, status)',
            'ALTER TABLE orders ADD INDEX idx_created_at (created_at)',
            'ALTER TABLE order_items ADD INDEX idx_product_quantity (product_id, quantity)',
            'ALTER TABLE user_behaviors ADD INDEX idx_user_action (user_id, action)',
            'ALTER TABLE user_behaviors ADD INDEX idx_created_at (created_at)',
            'ALTER TABLE user_points ADD INDEX idx_user_type (user_id, type)',
            'ALTER TABLE user_points ADD INDEX idx_expires_at (expires_at)',
        ];

        foreach ($indexes as $index) {
            try {
                DB::statement($index);
                $this->line('✓ Added index: '.substr($index, 0, 50).'...');
            } catch (\Exception $e) {
                $this->warn('⚠ Index may already exist: '.$e->getMessage());
            }
        }
    }

    private function analyzeTables()
    {
        $this->info('Analyzing tables...');

        $tables = [
            'products',
            'orders',
            'order_items',
            'users',
            'user_behaviors',
            'user_points',
            'payments',
        ];

        foreach ($tables as $table) {
            try {
                DB::statement("ANALYZE TABLE {$table}");
                $this->line("✓ Analyzed table: {$table}");
            } catch (\Exception $e) {
                $this->warn("⚠ Failed to analyze {$table}: ".$e->getMessage());
            }
        }
    }

    private function optimizeTables()
    {
        $this->info('Optimizing tables...');

        $tables = [
            'products',
            'orders',
            'order_items',
            'users',
            'user_behaviors',
            'user_points',
            'payments',
        ];

        foreach ($tables as $table) {
            try {
                DB::statement("OPTIMIZE TABLE {$table}");
                $this->line("✓ Optimized table: {$table}");
            } catch (\Exception $e) {
                $this->warn("⚠ Failed to optimize {$table}: ".$e->getMessage());
            }
        }
    }
}
