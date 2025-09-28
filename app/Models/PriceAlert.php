<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\ValidatesModel;
use Database\Factories\PriceAlertFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property float $target_price
 * @property bool $repeat_alert
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property User $user
 * @property Product $product
 *
 * @method static PriceAlertFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
class PriceAlert extends Model
{
    /** @phpstan-ignore-next-line */
    use HasFactory;

    use SoftDeletes;
    use ValidatesModel;

    /**
     * @var class-string<\Illuminate\Database\Eloquent\Factories\Factory<PriceAlert>>
     */
    protected static $factory = \Database\Factories\PriceAlertFactory::class;

    protected $fillable = [
        'user_id',
        'product_id',
        'target_price',
        'repeat_alert',
        'is_active',
    ];

    protected $casts = [
        'target_price' => 'decimal:2',
        'repeat_alert' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * The attributes that should be validated.
     *
     * @var array<string, string>
     */
    protected $rules = [
        'user_id' => 'required|exists:users,id',
        'product_id' => 'required|exists:products,id',
        'target_price' => 'required|numeric|min:0',
        'repeat_alert' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Validation errors.
     *
     * @var array<string, mixed>|null
     */
    protected $errors = null;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope a query to only include active alerts.
     *
     * @param  Builder<PriceAlert>  $query
     * @return Builder<PriceAlert>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include alerts for a specific user.
     *
     * @param  Builder<PriceAlert>  $query
     * @return Builder<PriceAlert>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include alerts for a specific product.
     *
     * @param  Builder<PriceAlert>  $query
     * @return Builder<PriceAlert>
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
     * Check if the price target has been reached.
     */
    public function isPriceTargetReached(float $currentPrice): bool
    {
        return $currentPrice <= $this->target_price;
    }

    /**
     * Activate the alert.
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the alert.
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }
}
