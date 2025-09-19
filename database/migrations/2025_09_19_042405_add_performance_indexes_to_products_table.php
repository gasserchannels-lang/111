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
            // Add indexes for better performance
            $table->index(['is_active', 'id']);
            $table->index(['category_id', 'is_active']);
            $table->index(['brand_id', 'is_active']);
            $table->index(['name']);
            $table->index(['price']);
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
