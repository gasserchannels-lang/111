<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BrandFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'slug' => $this->faker->slug,
            'logo_url' => $this->faker->imageUrl(200, 200, 'brands'),
            'website_url' => $this->faker->url,
            'is_active' => true,
        ];
    }
}
