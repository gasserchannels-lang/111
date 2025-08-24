<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'slug' => $this->faker->slug,
            'website_url' => $this->faker->url,
            'is_active' => true,
            'priority' => $this->faker->numberBetween(1, 10),
            'currency_id' => Currency::factory(),
        ];
    }
}
