<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Language;
use App\Models\PriceAlert;
use App\Models\PriceOffer;
use App\Models\Product;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use App\Models\UserLocaleSetting;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ModelRelationsTest extends TestCase
{
    #[DataProvider('modelRelationsProvider')]
    public function test_model_relations_exist(Model $model, array $relations): void
    {
        foreach ($relations as $relation) {
            $this->assertTrue(
                method_exists($model, $relation),
                class_basename($model)." is missing the '{$relation}' relation."
            );
        }
    }

    public static function modelRelationsProvider(): array
    {
        return [
            'Brand' => [new Brand, ['products']],
            'Category' => [new Category, ['products']],
            'Currency' => [new Currency, ['stores', 'languages']],
            'Language' => [new Language, ['userLocaleSettings', 'currencies']],
            'PriceAlert' => [new PriceAlert, ['user', 'product']],
            'PriceOffer' => [new PriceOffer, ['product', 'store']],
            'Product' => [new Product, ['brand', 'category', 'priceOffers', 'reviews', 'wishlists', 'priceAlerts']],
            'Review' => [new Review, ['user', 'product']],
            'Store' => [new Store, ['priceOffers', 'currency']],
            'User' => [new User, ['reviews', 'wishlists', 'priceAlerts', 'localeSetting']],
            'UserLocaleSetting' => [new UserLocaleSetting, ['user', 'language', 'currency']],
            'Wishlist' => [new Wishlist, ['user', 'product']],
        ];
    }
}
