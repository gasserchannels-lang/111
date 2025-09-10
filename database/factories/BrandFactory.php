<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BrandFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company.' Brand',
            'slug' => $this->faker->unique()->slug(2),
            'description' => $this->faker->sentence(),
            'logo_url' => $this->faker->imageUrl(200, 200),
            'website_url' => $this->faker->url(),
            'is_active' => true,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
