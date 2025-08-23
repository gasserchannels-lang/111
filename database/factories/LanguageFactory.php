<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Language>
 */
class LanguageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Language::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->country,
            'code' => $this->faker->unique()->languageCode,
            'native_name' => $this->faker->word,
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
