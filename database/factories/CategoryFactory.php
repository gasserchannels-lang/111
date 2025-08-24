<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->slug(),
            // ✅ الخطوة 1: إضافة الحقول الضرورية الأخرى
            'is_active' => true,
            'parent_id' => null, // الافتراضي هو أنها فئة رئيسية
        ];
    }
}
