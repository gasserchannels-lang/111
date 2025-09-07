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
                $productUrl = $store->website_url.'/product/'.strtolower($product->slug);
                PriceOffer::create([
                    'product_id' => $product->id,
                    'product_sku' => 'SKU-' . $product->id . '-' . random_int(1000, 9999),
                    'store_id' => $store->id,
                    'price' => random_int(500, 1500) + (random_int(0, 99) / 100),
                    'currency' => 'USD',
                    'product_url' => $productUrl,
                    'affiliate_url' => $store->generateAffiliateUrl($productUrl),
                    'in_stock' => random_int(0, 1) == 1,
                    'stock_quantity' => random_int(0, 100),
                    'condition' => 'new',
                    'rating' => random_int(35, 50) / 10,
                    'reviews_count' => random_int(10, 1000),
                    'image_url' => 'https://via.placeholder.com/300x300?text='.urlencode($product->name),
                    'specifications' => [
                        'brand' => explode(' ', $product->name)[0],
                        'model' => $product->name,
                    ],
                ]);
            }
        }
    }
}
