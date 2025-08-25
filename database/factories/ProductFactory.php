<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            // تم تبسيط الفاكتوري ليتوافق مع الأعمدة الأساسية فقط
        ];
    }
}
