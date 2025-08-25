<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceOfferFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'store_id' => Store::factory(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'url' => $this->faker->url,
            'in_stock' => $this->faker->boolean(80),
        ];
    }
}
