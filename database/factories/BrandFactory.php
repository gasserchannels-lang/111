<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BrandFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'slug' => $this->faker->slug,
            'description' => $this->faker->paragraph(),
            'logo_url' => $this->faker->imageUrl(200, 200, 'brands'),
            'website_url' => $this->faker->url,
            'is_active' => true,
        ];
    }
}
