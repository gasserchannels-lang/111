<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->languageCode,
            'name' => $this->faker->word,
            'native_name' => $this->faker->word,
            'is_default' => false,
        ];
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes): array => [
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_default' => true,
        ]);
    }
}
