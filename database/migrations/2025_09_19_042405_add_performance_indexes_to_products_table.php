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
        Schema::table('products', function (Blueprint $table) {
            // Add indexes for better performance (only if they don't exist)
            if (! Schema::hasIndex('products', 'products_is_active_id_index')) {
                $table->index(['is_active', 'id']);
            }
            if (! Schema::hasIndex('products', 'products_category_id_is_active_index')) {
                $table->index(['category_id', 'is_active']);
            }
            if (! Schema::hasIndex('products', 'products_brand_id_is_active_index')) {
                $table->index(['brand_id', 'is_active']);
            }
            if (! Schema::hasIndex('products', 'products_name_index')) {
                $table->index(['name']);
            }
            if (! Schema::hasIndex('products', 'products_price_index')) {
                $table->index(['price']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['is_active', 'id']);
            $table->dropIndex(['category_id', 'is_active']);
            $table->dropIndex(['brand_id', 'is_active']);
            $table->dropIndex(['name']);
            $table->dropIndex(['price']);
        });
    }
};
