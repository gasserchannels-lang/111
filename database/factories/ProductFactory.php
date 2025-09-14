<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product<\Database\Factories\ProductFactory>>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $words = $this->faker->unique()->words(3, true);

        return [
            'name' => (is_string($words) ? $words : '').' Product',
            'slug' => $this->faker->unique()->slug(3),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'image' => $this->faker->imageUrl(400, 400),
            'brand_id' => Brand::factory(),
            'category_id' => Category::factory(),
            'store_id' => Store::factory(),
            'is_active' => true,
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

    public function withPrice(float $price): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $price,
        ]);
    }

    public function withBrand(int $brandId): static
    {
        return $this->state(fn (array $attributes) => [
            'brand_id' => $brandId,
        ]);
    }

    public function withCategory(int $categoryId): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $categoryId,
        ]);
    }

    public function withStore(int $storeId): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => $storeId,
        ]);
    }
}
