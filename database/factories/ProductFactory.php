<?php
namespace Database\Factories;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
class ProductFactory extends Factory
{
    protected $model = Product::class;
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->slug,
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'is_active' => true,
            'brand_id' => Brand::factory(),
            'category_id' => Category::factory(), // تم إصلاح Category، لذا يمكننا استدعاؤها الآن
        ];
    }
}
