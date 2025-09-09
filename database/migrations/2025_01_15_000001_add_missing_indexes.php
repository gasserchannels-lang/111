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
        // Only add indexes if tables exist
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                // Add indexes for commonly queried columns
                if (!Schema::hasIndex('products', 'products_is_active_index')) {
                    $table->index('is_active');
                }
                if (!Schema::hasIndex('products', 'products_price_index')) {
                    $table->index('price');
                }
                if (!Schema::hasIndex('products', 'products_created_at_index')) {
                    $table->index('created_at');
                }
                if (!Schema::hasIndex('products', 'products_updated_at_index')) {
                    $table->index('updated_at');
                }
                if (!Schema::hasIndex('products', 'products_category_id_is_active_index')) {
                    $table->index(['category_id', 'is_active']);
                }
                if (!Schema::hasIndex('products', 'products_brand_id_is_active_index')) {
                    $table->index(['brand_id', 'is_active']);
                }
                if (!Schema::hasIndex('products', 'products_price_is_active_index')) {
                    $table->index(['price', 'is_active']);
                }
                if (!Schema::hasIndex('products', 'products_created_at_is_active_index')) {
                    $table->index(['created_at', 'is_active']);
                }
            });
        }

        if (Schema::hasTable('price_offers')) {
            Schema::table('price_offers', function (Blueprint $table) {
                // Add indexes for price comparison queries
                if (!Schema::hasIndex('price_offers', 'price_offers_price_index')) {
                    $table->index('price');
                }
                if (!Schema::hasIndex('price_offers', 'price_offers_is_available_index')) {
                    $table->index('is_available');
                }
                if (!Schema::hasIndex('price_offers', 'price_offers_product_id_is_available_index')) {
                    $table->index(['product_id', 'is_available']);
                }
                if (!Schema::hasIndex('price_offers', 'price_offers_store_id_is_available_index')) {
                    $table->index(['store_id', 'is_available']);
                }
                if (!Schema::hasIndex('price_offers', 'price_offers_price_is_available_index')) {
                    $table->index(['price', 'is_available']);
                }
                if (!Schema::hasIndex('price_offers', 'price_offers_created_at_index')) {
                    $table->index('created_at');
                }
            });
        }

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                // Add indexes for review queries
                if (!Schema::hasIndex('reviews', 'reviews_is_approved_index')) {
                    $table->index('is_approved');
                }
                if (!Schema::hasIndex('reviews', 'reviews_product_id_is_approved_index')) {
                    $table->index(['product_id', 'is_approved']);
                }
                if (!Schema::hasIndex('reviews', 'reviews_user_id_is_approved_index')) {
                    $table->index(['user_id', 'is_approved']);
                }
                if (!Schema::hasIndex('reviews', 'reviews_created_at_index')) {
                    $table->index('created_at');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Add indexes for user queries
                if (!Schema::hasIndex('users', 'users_is_admin_index')) {
                    $table->index('is_admin');
                }
                if (!Schema::hasIndex('users', 'users_is_active_index')) {
                    $table->index('is_active');
                }
                if (!Schema::hasIndex('users', 'users_created_at_index')) {
                    $table->index('created_at');
                }
                if (!Schema::hasIndex('users', 'users_is_admin_is_active_index')) {
                    $table->index(['is_admin', 'is_active']);
                }
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                // Add indexes for category queries
                if (!Schema::hasIndex('categories', 'categories_is_active_index')) {
                    $table->index('is_active');
                }
                if (!Schema::hasIndex('categories', 'categories_parent_id_index')) {
                    $table->index('parent_id');
                }
                if (!Schema::hasIndex('categories', 'categories_parent_id_is_active_index')) {
                    $table->index(['parent_id', 'is_active']);
                }
            });
        }

        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                // Add indexes for brand queries
                if (!Schema::hasIndex('brands', 'brands_is_active_index')) {
                    $table->index('is_active');
                }
                if (!Schema::hasIndex('brands', 'brands_created_at_index')) {
                    $table->index('created_at');
                }
            });
        }

        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
                // Add indexes for store queries
                if (!Schema::hasIndex('stores', 'stores_is_active_index')) {
                    $table->index('is_active');
                }
                if (!Schema::hasIndex('stores', 'stores_is_verified_index')) {
                    $table->index('is_verified');
                }
                if (!Schema::hasIndex('stores', 'stores_is_active_is_verified_index')) {
                    $table->index(['is_active', 'is_verified']);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['is_active']);
                $table->dropIndex(['price']);
                $table->dropIndex(['created_at']);
                $table->dropIndex(['updated_at']);
                $table->dropIndex(['category_id', 'is_active']);
                $table->dropIndex(['brand_id', 'is_active']);
                $table->dropIndex(['price', 'is_active']);
                $table->dropIndex(['created_at', 'is_active']);
            });
        }

        if (Schema::hasTable('price_offers')) {
            Schema::table('price_offers', function (Blueprint $table) {
                $table->dropIndex(['price']);
                $table->dropIndex(['is_available']);
                $table->dropIndex(['product_id', 'is_available']);
                $table->dropIndex(['store_id', 'is_available']);
                $table->dropIndex(['price', 'is_available']);
                $table->dropIndex(['created_at']);
            });
        }

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropIndex(['is_approved']);
                $table->dropIndex(['product_id', 'is_approved']);
                $table->dropIndex(['user_id', 'is_approved']);
                $table->dropIndex(['created_at']);
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['is_admin']);
                $table->dropIndex(['is_active']);
                $table->dropIndex(['created_at']);
                $table->dropIndex(['is_admin', 'is_active']);
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex(['is_active']);
                $table->dropIndex(['parent_id']);
                $table->dropIndex(['parent_id', 'is_active']);
            });
        }

        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->dropIndex(['is_active']);
                $table->dropIndex(['created_at']);
            });
        }

        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->dropIndex(['is_active']);
                $table->dropIndex(['is_verified']);
                $table->dropIndex(['is_active', 'is_verified']);
            });
        }
    }
};