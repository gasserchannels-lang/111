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
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->slug,
            'description' => $this->faker->paragraph,
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'image_url' => $this->faker->imageUrl(400, 400, 'products'),
            'model_number' => $this->faker->bothify('##??###'),
            'specifications' => json_encode(['color' => $this->faker->colorName]),
            'is_active' => true,
        ];
    }
}
