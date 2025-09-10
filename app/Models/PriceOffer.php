<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PriceOfferFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int    $id
 * @property int    $product_id
 * @property int    $store_id
 * @property float  $price
 * @property string $url
 * @property bool   $in_stock
 * @property-read Product $product
 * @property-read Store $store
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
     * @return BelongsTo<Product, PriceOffer>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Store, PriceOffer>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Scope a query to only include available offers.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope a query to only include offers for a specific product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include offers for a specific store.
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Get the lowest price for a product.
     */
    public static function lowestPriceForProduct($productId)
    {
        return static::where('product_id', $productId)
            ->where('is_available', true)
            ->min('price');
    }

    /**
     * Get the best offer for a product.
     */
    public static function bestOfferForProduct($productId)
    {
        return static::where('product_id', $productId)
            ->where('is_available', true)
            ->orderBy('price', 'asc')
            ->first();
    }

    /**
     * Mark the offer as unavailable.
     */
    public function markAsUnavailable()
    {
        $this->update(['is_available' => false]);
    }

    /**
     * Mark the offer as available.
     */
    public function markAsAvailable()
    {
        $this->update(['is_available' => true]);
    }

    /**
     * Update the price of the offer.
     */
    public function updatePrice($newPrice)
    {
        $this->update(['price' => $newPrice]);
    }

    /**
     * Get the price difference from original price.
     */
    public function getPriceDifferenceFromOriginal()
    {
        if (!$this->original_price) {
            return 0;
        }

        return $this->price - $this->original_price;
    }

    /**
     * Get the price difference percentage from original price.
     */
    public function getPriceDifferencePercentage()
    {
        if (!$this->original_price || $this->original_price == 0) {
            return 0;
        }

        return (($this->price - $this->original_price) / $this->original_price) * 100;
    }
}
