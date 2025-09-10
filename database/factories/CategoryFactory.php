<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true).' Category',
            'slug' => $this->faker->unique()->slug(2),
            'description' => $this->faker->sentence(),
            'parent_id' => null,
            'level' => 0,
            'is_active' => true,
        ];
    }
}
