<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property float $price
 * @property bool $is_active
 * @property int $category_id
 * @property int $brand_id
 *
 * @property-read Category $category
 * @property-read Brand $brand
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PriceAlert> $priceAlerts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Review> $reviews
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Wishlist> $wishlists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PriceOffer> $priceOffers
 *
 * @method static ProductFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
/**
 * @template TFactory of ProductFactory
 * @mixin TFactory
 */
class Product extends Model
{
    /**
     * @use HasFactory<ProductFactory>
     */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'is_active',
        'category_id',
        'brand_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // --- العلاقات ---
    /**
     * @return BelongsTo<Brand, Product>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return BelongsTo<Category, Product>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<PriceAlert, Product>
     */
    public function priceAlerts(): HasMany
    {
        return $this->hasMany(PriceAlert::class);
    }

    /**
     * @return HasMany<Review, Product>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return HasMany<Wishlist, Product>
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * @return HasMany<PriceOffer, Product>
     */
    public function priceOffers(): HasMany
    {
        return $this->hasMany(PriceOffer::class);
    }
}