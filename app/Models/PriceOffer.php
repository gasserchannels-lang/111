<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @template TFactory of \Illuminate\Database\Eloquent\Factories\Factory
 */
class PriceOffer extends Model
{
    /** @use HasFactory<\App\Models\PriceOffer> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'store_id',
        'price',
        'url',
        'in_stock',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Product, \App\Models\PriceOffer>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Store, \App\Models\PriceOffer>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
