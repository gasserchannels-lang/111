<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Currency::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->currencyCode.' Dollar',
            'code' => $this->faker->unique()->currencyCode,
            'symbol' => '$',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
