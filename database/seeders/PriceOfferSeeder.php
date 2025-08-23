<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PriceOfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleProducts = [
            [
                'name' => 'iPhone 15 Pro 128GB',
                'code' => 'IPHONE15PRO128',
                'sku' => 'APL-IP15P-128',
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra 256GB',
                'code' => 'GALAXYS24ULTRA256',
                'sku' => 'SAM-GS24U-256',
            ],
            [
                'name' => 'MacBook Air M3 13-inch',
                'code' => 'MACBOOKAIRM3',
                'sku' => 'APL-MBA-M3-13',
            ],
            [
                'name' => 'Sony WH-1000XM5 Headphones',
                'code' => 'SONYWH1000XM5',
                'sku' => 'SNY-WH1000XM5',
            ],
        ];

        $stores = \App\Models\Store::all();

        foreach ($sampleProducts as $product) {
            foreach ($stores->take(5) as $store) { // أول 5 متاجر لكل منتج
                \App\Models\PriceOffer::create([
                    'product_name' => $product['name'],
                    'product_code' => $product['code'],
                    'product_sku' => $product['sku'],
                    'store_id' => $store->id,
                    'price' => rand(500, 1500) + (rand(0, 99) / 100), // سعر عشوائي
                    'currency' => 'USD',
                    'product_url' => $store->website_url.'/product/'.strtolower($product['code']),
                    'affiliate_url' => $store->generateAffiliateUrl($store->website_url.'/product/'.strtolower($product['code'])),
                    'in_stock' => rand(0, 1) == 1,
                    'stock_quantity' => rand(0, 100),
                    'condition' => 'new',
                    'rating' => rand(35, 50) / 10, // تقييم من 3.5 إلى 5.0
                    'reviews_count' => rand(10, 1000),
                    'image_url' => 'https://via.placeholder.com/300x300?text='.urlencode($product['name']),
                    'specifications' => [
                        'brand' => explode(' ', $product['name'])[0],
                        'model' => $product['name'],
                        'color' => ['Black', 'White', 'Blue', 'Red'][rand(0, 3)],
                    ],
                    'last_updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Price offers seeded successfully!');
    }
}
