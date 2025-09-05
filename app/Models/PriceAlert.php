<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @template TFactory of \Illuminate\Database\Eloquent\Factories\Factory
 *
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\User $user
 */
class PriceAlert extends Model
{
    /** @use HasFactory<\App\Models\PriceAlert> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'target_price',
        'repeat_alert',
        'is_active',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\PriceAlert>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Product, \App\Models\PriceAlert>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
