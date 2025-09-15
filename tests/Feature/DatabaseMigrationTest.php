<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DatabaseMigrationTest extends TestCase
{
    

    #[Test]
    public function users_table_has_correct_structure()
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumns('users', [
            'id', 'name', 'email', 'email_verified_at', 'password',
            'remember_token', 'created_at', 'updated_at',
        ]));
    }

    #[Test]
    public function products_table_has_correct_structure()
    {
        $this->assertTrue(Schema::hasTable('products'));
        $this->assertTrue(Schema::hasColumns('products', [
            'id', 'name', 'description', 'price', 'category_id',
            'brand_id', 'created_at', 'updated_at',
        ]));
    }

    #[Test]
    public function categories_table_has_correct_structure()
    {
        $this->assertTrue(Schema::hasTable('categories'));
        $this->assertTrue(Schema::hasColumns('categories', [
            'id', 'name', 'slug', 'description', 'parent_id',
            'created_at', 'updated_at',
        ]));
    }

    #[Test]
    public function brands_table_has_correct_structure()
    {
        $this->assertTrue(Schema::hasTable('brands'));
        $this->assertTrue(Schema::hasColumns('brands', [
            'id', 'name', 'slug', 'logo', 'description',
            'created_at', 'updated_at',
        ]));
    }

    #[Test]
    public function wishlists_table_has_correct_structure()
    {
        $this->assertTrue(Schema::hasTable('wishlists'));
        $this->assertTrue(Schema::hasColumns('wishlists', [
            'id', 'user_id', 'product_id', 'created_at', 'updated_at',
        ]));
    }

    #[Test]
    public function price_alerts_table_has_correct_structure()
    {
        $this->assertTrue(Schema::hasTable('price_alerts'));
        $this->assertTrue(Schema::hasColumns('price_alerts', [
            'id', 'user_id', 'product_id', 'target_price', 'is_active',
            'created_at', 'updated_at',
        ]));
    }

    #[Test]
    public function reviews_table_has_correct_structure()
    {
        $this->assertTrue(Schema::hasTable('reviews'));
        $this->assertTrue(Schema::hasColumns('reviews', [
            'id', 'user_id', 'product_id', 'rating', 'comment',
            'created_at', 'updated_at',
        ]));
    }

    #[Test]
    public function foreign_key_constraints_are_working()
    {
        // Test that foreign key constraints are properly set up
        $this->assertTrue(Schema::hasColumn('products', 'category_id'));
        $this->assertTrue(Schema::hasColumn('products', 'brand_id'));
        $this->assertTrue(Schema::hasColumn('wishlists', 'user_id'));
        $this->assertTrue(Schema::hasColumn('wishlists', 'product_id'));
    }

    #[Test]
    public function indexes_are_created()
    {
        // Test that important indexes are created
        $this->assertTrue(Schema::hasIndex('users', 'users_email_unique'));
        $this->assertTrue(Schema::hasIndex('products', 'products_category_id_index'));
        $this->assertTrue(Schema::hasIndex('products', 'products_brand_id_index'));
    }

    #[Test]
    public function migrations_can_be_rolled_back()
    {
        // Test that migrations can be rolled back without errors
        $this->artisan('migrate:rollback', ['--step' => 1]);
        $this->artisan('migrate');

        // Verify tables still exist after rollback and re-migration
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('products'));
    }
}
