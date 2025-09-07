<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WishlistFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property-read User $user
 * @property-read Product $product
 *
 * @method static WishlistFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
/**
 * @template TFactory of WishlistFactory
 *
 * @mixin TFactory
 */
class Wishlist extends Model
{
    /**
     * @use HasFactory<WishlistFactory>
     */
    use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wishlist>>;

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    /**
     * @return BelongsTo<User, Wishlist>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Product, Wishlist>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
