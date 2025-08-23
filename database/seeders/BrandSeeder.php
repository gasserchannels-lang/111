<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Apple',
            'Samsung',
            'Sony',
            'Nike',
            'Adidas',
            'IKEA',
            'LG',
            'Dell',
            'HP',
            'Canon',
            'Nikon',
            'Bose',
            'JBL',
            'Logitech',
        ];

        foreach ($brands as $brandName) {
            Brand::create([
                'name' => $brandName,
                'slug' => Str::slug($brandName),
            ]);
        }

        $this->command->info('Brands seeded successfully!');
    }
}
