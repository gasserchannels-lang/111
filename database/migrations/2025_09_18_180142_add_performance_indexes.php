<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        });

        // Create index on TEXT column with specified length (MySQL only)
        if (DB::getDriverName() === 'mysql' && ! Schema::hasIndex('products', 'products_description_is_active_index')) {
            DB::statement('ALTER TABLE products ADD INDEX products_description_is_active_index(description(255), is_active)');
        }

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

            // --- START: MODIFIED CODE ---
            // The 'is_active' column does not seem to exist on the users table, so we will skip this index.
            // if (! Schema::hasIndex('users', 'users_is_active_index')) {
            //     $table->index(['is_active']);
            // }
            // --- END: MODIFIED CODE ---

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
            $table->dropIndex('products_is_active_name_index');
            $table->dropIndex('products_is_active_category_id_index');
            $table->dropIndex('products_is_active_brand_id_index');
            $table->dropIndex('products_is_active_price_index');
            $table->dropIndex('products_name_is_active_index');
            $table->dropIndex('products_description_is_active_index');
        });

        Schema::table('price_offers', function (Blueprint $table) {
            $table->dropIndex('price_offers_product_id_is_available_index');
            $table->dropIndex('price_offers_store_id_is_available_index');
            $table->dropIndex('price_offers_price_is_available_index');
            $table->dropIndex('price_offers_product_id_price_index');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropIndex('stores_is_active_country_code_index');
            $table->dropIndex('stores_country_code_is_active_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_email_index');
            // --- START: MODIFIED CODE ---
            // Also comment out the dropIndex for the non-existent index
            // $table->dropIndex('users_is_active_index');
            // --- END: MODIFIED CODE ---
            $table->dropIndex('users_is_admin_index');
        });
    }
};
