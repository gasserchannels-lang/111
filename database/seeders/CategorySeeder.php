<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Parent categories
        $parentCategories = [
            'Electronics' => [
                'Smartphones',
                'Laptops',
                'Accessories',
            ],
            'Fashion' => [
                'Men\'s Clothing',
                'Women\'s Clothing',
                'Shoes',
            ],
            'Home & Garden' => [
                'Furniture',
                'Kitchen',
                'Decor',
            ],
            'Sports' => [
                'Fitness',
                'Outdoor',
                'Team Sports',
            ],
        ];

        foreach ($parentCategories as $parentName => $children) {
            $parent = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'parent_id' => null,
                'level' => 1,
            ]);

            foreach ($children as $childName) {
                Category::create([
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'parent_id' => $parent->id,
                    'level' => 2,
                ]);
            }
        }

        $this->command->info('Categories seeded successfully!');
    }
}
