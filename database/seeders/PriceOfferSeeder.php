<?php

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
                $productUrl = $store->website_url.'/product/'.strtolower($product['code']);
                PriceOffer::create([
                    'product_id' => $product->id,
                    'product_sku' => $product['sku'],
                    'store_id' => $store->id,
                    'price' => rand(500, 1500) + (rand(0, 99) / 100),
                    'currency' => 'USD',
                    'product_url' => $productUrl,
                    'affiliate_url' => $store->generateAffiliateUrl($productUrl),
                    'in_stock' => rand(0, 1) == 1,
                    'stock_quantity' => rand(0, 100),
                    'condition' => 'new',
                    'rating' => rand(35, 50) / 10,
                    'reviews_count' => rand(10, 1000),
                    'image_url' => 'https://via.placeholder.com/300x300?text='.urlencode($product['name']),
                    'specifications' => [
                        'brand' => explode(' ', $product['name'])[0],
                        'model' => $product['name'],
                    ],
                ]);
            }
        }
    }
}
