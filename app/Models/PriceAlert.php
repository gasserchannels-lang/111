<?php

declare(strict_types=1);

namespace App\Models;

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
    use HasFactory;

    use SoftDeletes;

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
    protected $errors;

    /**
     * @return BelongsTo<User<\Database\Factories\UserFactory>, PriceAlert<\Database\Factories\PriceAlertFactory>>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Product<\Database\Factories\ProductFactory>, PriceAlert<\Database\Factories\PriceAlertFactory>>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope a query to only include active alerts.
     *
     * @param  Builder<PriceAlert<\Database\Factories\PriceAlertFactory>>  $query
     * @return Builder<PriceAlert<\Database\Factories\PriceAlertFactory>>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include alerts for a specific user.
     *
     * @param  Builder<PriceAlert<\Database\Factories\PriceAlertFactory>>  $query
     * @return Builder<PriceAlert<\Database\Factories\PriceAlertFactory>>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include alerts for a specific product.
     *
     * @param  Builder<PriceAlert<\Database\Factories\PriceAlertFactory>>  $query
     * @return Builder<PriceAlert<\Database\Factories\PriceAlertFactory>>
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
