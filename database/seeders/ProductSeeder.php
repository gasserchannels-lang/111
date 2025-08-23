<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $brands = Brand::all();

        if ($categories->isEmpty() || $brands->isEmpty()) {
            $this->command->error('Please run CategorySeeder and BrandSeeder first!');

            return;
        }

        $products = [
            [
                'name' => 'Wireless Bluetooth Headphones',
                'description' => 'High-quality wireless headphones with noise cancellation and 30-hour battery life.',
                'price' => 89.99,
                'compare_at_price' => 129.99,
                'is_active' => true,
            ],
            [
                'name' => 'Smart Fitness Watch',
                'description' => 'Advanced fitness tracker with heart rate monitor, GPS, and water resistance.',
                'price' => 199.99,
                'compare_at_price' => 249.99,
                'is_active' => true,
            ],
            [
                'name' => 'Portable Power Bank',
                'description' => '20000mAh power bank with fast charging and multiple USB ports.',
                'price' => 49.99,
                'compare_at_price' => 69.99,
                'is_active' => true,
            ],
            [
                'name' => 'Wireless Gaming Mouse',
                'description' => 'Ergonomic gaming mouse with RGB lighting and adjustable DPI.',
                'price' => 79.99,
                'compare_at_price' => 99.99,
                'is_active' => true,
            ],
            [
                'name' => 'Mechanical Keyboard',
                'description' => 'Premium mechanical keyboard with Cherry MX switches and backlighting.',
                'price' => 149.99,
                'compare_at_price' => 199.99,
                'is_active' => true,
            ],
            [
                'name' => '4K Webcam',
                'description' => 'Professional webcam with 4K resolution and built-in microphone.',
                'price' => 129.99,
                'compare_at_price' => 179.99,
                'is_active' => true,
            ],
            [
                'name' => 'Bluetooth Speaker',
                'description' => 'Portable Bluetooth speaker with 360-degree sound and waterproof design.',
                'price' => 69.99,
                'compare_at_price' => 89.99,
                'is_active' => true,
            ],
            [
                'name' => 'USB-C Hub',
                'description' => 'Multi-port USB-C hub with HDMI, USB-A, and SD card reader.',
                'price' => 39.99,
                'compare_at_price' => 59.99,
                'is_active' => true,
            ],
        ];

        foreach ($products as $productData) {
            Product::create([
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']),
                'description' => $productData['description'],
                'price' => $productData['price'],
                'compare_at_price' => $productData['compare_at_price'],
                'is_active' => $productData['is_active'],
                'category_id' => $categories->random()->id,
                'brand_id' => $brands->random()->id,
            ]);
        }

        $this->command->info('Products seeded successfully!');
    }
}
