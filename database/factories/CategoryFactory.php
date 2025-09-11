<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category<\Database\Factories\CategoryFactory>>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $words = $this->faker->unique()->words(2, true);

        return [
            'name' => $words.' Category',
            'slug' => $this->faker->unique()->slug(2),
            'description' => $this->faker->sentence(),
            'parent_id' => null,
            'level' => 0,
            'is_active' => true,
        ];
    }
}
