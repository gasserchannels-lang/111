<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraph(),
            'rating' => $this->faker->numberBetween(1, 5),
            'is_verified_purchase' => $this->faker->boolean(30),
            'is_approved' => true,
            'helpful_count' => $this->faker->numberBetween(0, 50),
        ];
    }
}
