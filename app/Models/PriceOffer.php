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
    ];

    protected $casts = [
        'specifications' => 'array',
        'in_stock' => 'boolean',
        'rating' => 'decimal:1',
        'price' => 'decimal:2',
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
}
