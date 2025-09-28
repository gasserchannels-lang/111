<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PriceOfferFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id
 * @property int $store_id
 * @property float $price
 * @property bool $in_stock
 * @property Product $product
 * @property Store $store
 *
 * @method static PriceOfferFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
class PriceOffer extends Model
{
    /** @phpstan-ignore-next-line */
    use HasFactory;

    /**
     * @var class-string<\Illuminate\Database\Eloquent\Factories\Factory<PriceOffer>>
     */
    protected static $factory = \Database\Factories\PriceOfferFactory::class;

    protected $fillable = [
        'product_id',
        'product_sku',
        'store_id',
        'price',
        'currency',
        'product_url',
        'affiliate_url',
        'in_stock',
        'stock_quantity',
        'condition',
        'rating',
        'reviews_count',
        'image_url',
        'specifications',
        'is_available',
        'original_price',
    ];

    protected $casts = [
        'specifications' => 'array',
        'in_stock' => 'boolean',
        'is_available' => 'boolean',
        'rating' => 'decimal:1',
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Scope a query to only include available offers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PriceOffer>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PriceOffer>
     */
    public function scopeAvailable(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope a query to only include offers for a specific product.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PriceOffer>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PriceOffer>
     */
    public function scopeForProduct(\Illuminate\Database\Eloquent\Builder $query, int $productId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include offers for a specific store.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PriceOffer>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PriceOffer>
     */
    public function scopeForStore(\Illuminate\Database\Eloquent\Builder $query, int $storeId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Get the lowest price for a product.
     */
    public static function lowestPriceForProduct(int $productId): ?float
    {
        $minPrice = static::where('product_id', $productId)
            ->where('is_available', true)
            ->min('price');

        return is_numeric($minPrice) ? (float) $minPrice : null;
    }

    /**
     * Get the best offer for a product.
     */
    public static function bestOfferForProduct(int $productId): ?self
    {
        return static::where('product_id', $productId)
            ->where('is_available', true)
            ->orderBy('price', 'asc')
            ->first();
    }

    /**
     * Mark the offer as unavailable.
     */
    public function markAsUnavailable(): bool
    {
        return $this->update(['is_available' => false]);
    }

    /**
     * Mark the offer as available.
     */
    public function markAsAvailable(): bool
    {
        return $this->update(['is_available' => true]);
    }

    /**
     * Update the price of the offer.
     */
    public function updatePrice(float $newPrice): bool
    {
        return $this->update(['price' => $newPrice]);
    }

    /**
     * Get the price difference from original price.
     */
    public function getPriceDifferenceFromOriginal(): float
    {
        if (! $this->original_price) {
            return 0.0;
        }

        return $this->price - $this->original_price;
    }

    /**
     * Get the price difference percentage from original price.
     */
    public function getPriceDifferencePercentage(): float
    {
        if (! $this->original_price || (float) $this->original_price === 0.0) {
            return 0.0;
        }

        return (($this->price - $this->original_price) / $this->original_price) * 100;
    }
}
