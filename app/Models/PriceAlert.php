<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PriceAlertFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property float $target_price
 * @property bool $repeat_alert
 * @property bool $is_active
 * @property-read User $user
 * @property-read Product $product
 *
 * @method static PriceAlertFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
/**
 * @template TFactory of PriceAlertFactory
 *
 * @mixin TFactory
 */
class PriceAlert extends Model
{
    /**
     * @use HasFactory<PriceAlertFactory>
     */
    use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PriceAlert>>;

    protected $fillable = [
        'user_id',
        'product_id',
        'target_price',
        'repeat_alert',
        'is_active',
    ];

    /**
     * @return BelongsTo<User, PriceAlert>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Product, PriceAlert>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
