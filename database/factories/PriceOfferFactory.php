<?php
namespace Database\Factories;
use App\Models\PriceOffer;
use Illuminate\Database\Eloquent\Factories\Factory;
class PriceOfferFactory extends Factory
{
    protected $model = PriceOffer::class;
    public function definition(): array
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'store_id' => \App\Models\Store::factory(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'url' => $this->faker->url,
        ];
    }
}
