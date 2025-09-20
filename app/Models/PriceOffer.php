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
 * @property string $url
 * @property bool $in_stock
 * @property-read Product<Database\Factories\ProductFactory> $product
 * @property-read Store<Database\Factories\StoreFactory> $store
 *
 * @method static PriceOfferFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
/**
 * @template TFactory of PriceOfferFactory
 *
 * @mixin TFactory
 */
class PriceOffer extends Model
{
    /**
     * @use HasFactory<PriceOfferFactory>
     */
    use HasFactory;

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
        'url',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Product>
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Store>
     */
    public function store(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Scope a query to only include available offers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PriceOffer<\Database\Factories\PriceOfferFactory>>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PriceOffer<\Database\Factories\PriceOfferFactory>>
     */
    public function scopeAvailable(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope a query to only include offers for a specific product.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PriceOffer<\Database\Factories\PriceOfferFactory>>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PriceOffer<\Database\Factories\PriceOfferFactory>>
     */
    public function scopeForProduct(\Illuminate\Database\Eloquent\Builder $query, int $productId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include offers for a specific store.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PriceOffer<\Database\Factories\PriceOfferFactory>>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PriceOffer<\Database\Factories\PriceOfferFactory>>
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
        return static::where('product_id', $productId)
            ->where('is_available', true)
            ->min('price');
    }

    /**
     * Get the best offer for a product.
     *
     * @return PriceOffer<\Database\Factories\PriceOfferFactory>|null
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
