<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PriceOffer;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;

class PriceOfferSeeder extends Seeder
{
    public function run(): void
    {
        PriceOffer::truncate();
        $products = Product::all();
        $stores = Store::all();

        foreach ($stores as $store) {
            foreach ($products as $product) {
                $storeWebsiteUrl = is_string($store->website_url) ? $store->website_url : '';
                $productSlug = is_string($product->slug) ? $product->slug : '';
                $productUrl = $storeWebsiteUrl.'/product/'.strtolower($productSlug);

                $productId = is_numeric($product->id) ? (int) $product->id : 0;
                $storeId = is_numeric($store->id) ? (int) $store->id : 0;

                $currency = is_string(config('coprra.default_currency')) ? config('coprra.default_currency') : 'USD';
                $condition = is_string(config('coprra.default_condition')) ? config('coprra.default_condition') : 'new';
                $appUrl = is_string(config('app.url')) ? config('app.url') : 'http://localhost';

                $productName = is_string($product->name) ? $product->name : 'Product';
                $affiliateUrl = method_exists($store, 'generateAffiliateUrl') ? $store->generateAffiliateUrl($productUrl) : $productUrl;

                PriceOffer::create([
                    'product_id' => $productId,
                    'product_sku' => 'SKU-'.$productId.'-'.random_int(1000, 9999),
                    'store_id' => $storeId,
                    'price' => random_int(500, 1500) + (random_int(0, 99) / 100),
                    'currency' => $currency,
                    'product_url' => $productUrl,
                    'affiliate_url' => is_string($affiliateUrl) ? $affiliateUrl : $productUrl,
                    'in_stock' => random_int(0, 1) == 1,
                    'stock_quantity' => random_int(0, 100),
                    'condition' => $condition,
                    'rating' => random_int(35, 50) / 10,
                    'reviews_count' => random_int(10, 1000),
                    'image_url' => $appUrl.'/images/placeholder/300x300?text='.urlencode($productName),
                    'specifications' => [
                        'brand' => explode(' ', $productName)[0] ?? '',
                        'model' => $productName,
                    ],
                ]);
            }
        }
    }
}
