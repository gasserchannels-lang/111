<?php

declare(strict_types=1);

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
            'product_sku' => 'SKU-' . $this->faker->unique()->numberBetween(1000, 9999),
            'store_id' => Store::factory(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP', 'SAR', 'AED']),
            'product_url' => $this->faker->url,
            'affiliate_url' => $this->faker->url,
            'in_stock' => $this->faker->boolean(80),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'condition' => $this->faker->randomElement(['new', 'used', 'refurbished']),
            'rating' => $this->faker->randomFloat(1, 1.0, 5.0),
            'reviews_count' => $this->faker->numberBetween(0, 1000),
            'image_url' => $this->faker->imageUrl(300, 300, 'products'),
            'specifications' => [
                'brand' => $this->faker->company,
                'model' => $this->faker->word,
                'color' => $this->faker->colorName,
                'weight' => $this->faker->numberBetween(100, 5000) . 'g',
            ],
        ];
    }
}
