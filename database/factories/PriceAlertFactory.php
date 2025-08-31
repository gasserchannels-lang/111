<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceAlertFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'target_price' => $this->faker->randomFloat(2, 10, 1000),
            'repeat_alert' => $this->faker->boolean,
            'is_active' => true,
        ];
    }
}
