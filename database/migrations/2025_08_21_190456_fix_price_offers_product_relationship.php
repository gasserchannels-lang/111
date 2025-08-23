<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // أولاً، نحتاج إلى ربط price_offers الموجودة بالمنتجات
        // بناءً على product_name
        $priceOffers = DB::table('price_offers')->get();
        
        foreach ($priceOffers as $offer) {
            // البحث عن المنتج المطابق بناءً على الاسم
            $product = DB::table('products')
                ->where('name', 'LIKE', '%' . $offer->product_name . '%')
                ->first();
            
            if ($product) {
                // تحديث product_id
                DB::table('price_offers')
                    ->where('id', $offer->id)
                    ->update(['product_id' => $product->id]);
            }
        }
        
        // الآن نجعل product_id مطلوب
        Schema::table('price_offers', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('price_offers', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->change();
        });
    }
};
