<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add performance indexes for products table
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasIndex('products', 'products_is_active_name_index')) {
                $table->index(['is_active', 'name']);
            }
            if (! Schema::hasIndex('products', 'products_is_active_category_id_index')) {
                $table->index(['is_active', 'category_id']);
            }
            if (! Schema::hasIndex('products', 'products_is_active_brand_id_index')) {
                $table->index(['is_active', 'brand_id']);
            }
            if (! Schema::hasIndex('products', 'products_is_active_price_index')) {
                $table->index(['is_active', 'price']);
            }
            if (! Schema::hasIndex('products', 'products_name_is_active_index')) {
                $table->index(['name', 'is_active']);
            }
            if (! Schema::hasIndex('products', 'products_description_is_active_index')) {
                $table->index(['description', 'is_active']);
            }
        });

        // Add performance indexes for price_offers table
        Schema::table('price_offers', function (Blueprint $table) {
            if (! Schema::hasIndex('price_offers', 'price_offers_product_id_is_available_index')) {
                $table->index(['product_id', 'is_available']);
            }
            if (! Schema::hasIndex('price_offers', 'price_offers_store_id_is_available_index')) {
                $table->index(['store_id', 'is_available']);
            }
            if (! Schema::hasIndex('price_offers', 'price_offers_price_is_available_index')) {
                $table->index(['price', 'is_available']);
            }
            if (! Schema::hasIndex('price_offers', 'price_offers_product_id_price_index')) {
                $table->index(['product_id', 'price']);
            }
        });

        // Add performance indexes for stores table
        Schema::table('stores', function (Blueprint $table) {
            if (! Schema::hasIndex('stores', 'stores_is_active_country_code_index')) {
                $table->index(['is_active', 'country_code']);
            }
            if (! Schema::hasIndex('stores', 'stores_country_code_is_active_index')) {
                $table->index(['country_code', 'is_active']);
            }
        });

        // Add performance indexes for users table
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasIndex('users', 'users_email_index')) {
                $table->index(['email']);
            }
            if (! Schema::hasIndex('users', 'users_is_active_index')) {
                $table->index(['is_active']);
            }
            if (! Schema::hasIndex('users', 'users_is_admin_index')) {
                $table->index(['is_admin']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop performance indexes
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'name']);
            $table->dropIndex(['is_active', 'category_id']);
            $table->dropIndex(['is_active', 'brand_id']);
            $table->dropIndex(['is_active', 'price']);
            $table->dropIndex(['name', 'is_active']);
            $table->dropIndex(['description', 'is_active']);
        });

        Schema::table('price_offers', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'is_available']);
            $table->dropIndex(['store_id', 'is_available']);
            $table->dropIndex(['price', 'is_available']);
            $table->dropIndex(['product_id', 'price']);
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'country_code']);
            $table->dropIndex(['country_code', 'is_active']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['is_admin']);
        });
    }
};
