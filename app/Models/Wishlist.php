<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WishlistFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property string|null $notes
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read User<Database\Factories\UserFactory> $user
 * @property-read Product<Database\Factories\ProductFactory> $product
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
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'product_id',
        'notes',
    ];

    /**
     * @var array<string, mixed>|null
     */
    protected $errors;

    /**
     * The attributes that should be validated.
     *
     * @var array<string, string>
     */
    protected $rules = [
        'user_id' => 'required|exists:users,id',
        'product_id' => 'required|exists:products,id',
        'notes' => 'nullable|string|max:1000',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Product>
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope a query to only include wishlist items for a specific user.
     *
     * @param  Builder<Wishlist<\Database\Factories\WishlistFactory>>  $query
     * @return Builder<Wishlist<\Database\Factories\WishlistFactory>>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include wishlist items for a specific product.
     *
     * @param  Builder<Wishlist<\Database\Factories\WishlistFactory>>  $query
     * @return Builder<Wishlist<\Database\Factories\WishlistFactory>>
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Get validation rules for the model.
     *
     * @return array<string, mixed>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Validate the model attributes.
     */
    public function validate(): bool
    {
        $validator = validator($this->attributes, $this->getRules());

        if ($validator->fails()) {
            $this->errors = $validator->errors()->toArray();

            return false;
        }

        return true;
    }

    /**
     * Get validation errors.
     *
     * @return array<string, mixed>
     */
    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    /**
     * Check if a product is in user's wishlist.
     */
    public static function isProductInWishlist(int $userId, int $productId): bool
    {
        return static::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Add a product to user's wishlist.
     *
     * @return Wishlist<\Database\Factories\WishlistFactory>
     */
    public static function addToWishlist(int $userId, int $productId, ?string $notes = null): self
    {
        return static::create([
            'user_id' => $userId,
            'product_id' => $productId,
            'notes' => $notes,
        ]);
    }

    /**
     * Remove a product from user's wishlist.
     */
    public static function removeFromWishlist(int $userId, int $productId): bool
    {
        return static::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete() > 0;
    }

    /**
     * Get wishlist count for a user.
     */
    public static function getWishlistCount(int $userId): int
    {
        return static::where('user_id', $userId)->count();
    }
}
