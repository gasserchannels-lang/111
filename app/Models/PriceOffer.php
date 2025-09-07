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
    use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PriceOffer>>;

    protected $fillable = [
        'product_id',
        'store_id',
        'price',
        'url',
        'in_stock',
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
