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
        Schema::create('price_offers', function (Blueprint $table) {
            $table->id();
            $table->string('product_name'); // اسم المنتج
            $table->string('product_code')->nullable(); // كود المنتج
            $table->string('product_sku')->nullable(); // SKU المنتج
            $table->foreignId('store_id')->constrained()->onDelete('cascade'); // المتجر
            $table->decimal('price', 10, 2); // السعر
            $table->string('currency', 3)->default('USD'); // العملة
            $table->string('product_url'); // رابط المنتج في المتجر
            $table->string('affiliate_url')->nullable(); // رابط الأفلييت
            $table->boolean('in_stock')->default(true); // متوفر في المخزن
            $table->integer('stock_quantity')->nullable(); // كمية المخزن
            $table->string('condition')->default('new'); // حالة المنتج (جديد، مستعمل، إلخ)
            $table->decimal('rating', 3, 2)->nullable(); // تقييم المنتج
            $table->integer('reviews_count')->default(0); // عدد المراجعات
            $table->string('image_url')->nullable(); // رابط صورة المنتج
            $table->json('specifications')->nullable(); // مواصفات المنتج
            $table->timestamp('last_updated_at')->useCurrent(); // آخر تحديث للسعر
            $table->timestamps();
            
            // فهارس للبحث السريع
            $table->index(['product_name', 'product_code']);
            $table->index(['store_id', 'in_stock']);
            $table->index('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_offers');
    }
};
