<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->lexify('???'),
            'name' => $this->faker->unique()->words(2, true),
            'symbol' => ['$', '€', '£', '¥', '₹'][array_rand(['$', '€', '£', '¥', '₹'])],
            'is_active' => true,
            'is_default' => false,
            'exchange_rate' => $this->faker->randomFloat(4, 0.1, 10.0),
            'decimal_places' => 2,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function withCode(string $code): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => $code,
        ]);
    }

    public function withSymbol(string $symbol): static
    {
        return $this->state(fn (array $attributes) => [
            'symbol' => $symbol,
        ]);
    }

    public function withExchangeRate(float $rate): static
    {
        return $this->state(fn (array $attributes) => [
            'exchange_rate' => $rate,
        ]);
    }
}
