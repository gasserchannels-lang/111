<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->slug,
            'description' => $this->faker->sentence,
            'image_url' => $this->faker->imageUrl(300, 300, 'categories'),
            'is_active' => true,
        ];
    }
}
