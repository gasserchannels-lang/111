<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'slug' => $this->faker->slug,
            'website_url' => $this->faker->url,
            'logo_url' => $this->faker->imageUrl(150, 150, 'stores'),
            'is_active' => true,
            'country' => 'Egypt',
        ];
    }
}
