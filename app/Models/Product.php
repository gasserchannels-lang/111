<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property float $price
 * @property string|null $image
 * @property bool $is_active
 * @property bool $is_featured
 * @property int $stock_quantity
 * @property int $category_id
 * @property int $brand_id
 * @property-read Category $category
 * @property-read Brand $brand
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PriceAlert> $priceAlerts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Review> $reviews
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Wishlist> $wishlists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PriceOffer> $priceOffers
 *
 * @method static ProductFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
/**
 * @template TFactory of ProductFactory
 *
 * @mixin TFactory
 */
class Product extends Model
{
    /**
     * @use HasFactory<ProductFactory>
     */
    use HasFactory {
        factory as baseFactory;
    }

    use SoftDeletes;

    /**
     * Create a new factory instance for the model.
     *
     * @param  array<string, mixed>  $state
     */
    public static function factory(?int $count = null, array $state = []): ProductFactory
    {
        return static::baseFactory($count, $state)->connection('testing');
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'image',
        'is_active',
        'is_featured',
        'stock_quantity',
        'category_id',
        'brand_id',
        'store_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'stock_quantity' => 'integer',
    ];

    /**
     * @var array<string, string>|null
     */
    protected $errors;

    // --- العلاقات ---
    /**
     * @return BelongsTo<Brand<\Database\Factories\BrandFactory>, Product<\Database\Factories\ProductFactory>>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class)->select(['id', 'name']);
    }

    /**
     * @return BelongsTo<Category<\Database\Factories\CategoryFactory>, Product<\Database\Factories\ProductFactory>>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->select(['id', 'name']);
    }

    /**
     * @return HasMany<PriceAlert<\Database\Factories\PriceAlertFactory>, Product<\Database\Factories\ProductFactory>>
     */
    public function priceAlerts(): HasMany
    {
        return $this->hasMany(PriceAlert::class);
    }

    /**
     * @return HasMany<Review<\Database\Factories\ReviewFactory>, Product<\Database\Factories\ProductFactory>>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return HasMany<Wishlist<\Database\Factories\WishlistFactory>, Product<\Database\Factories\ProductFactory>>
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * @return HasMany<PriceOffer<\Database\Factories\PriceOfferFactory>, Product<\Database\Factories\ProductFactory>>
     */
    public function priceOffers(): HasMany
    {
        return $this->hasMany(PriceOffer::class);
    }

    /**
     * @return BelongsTo<Store<\Database\Factories\StoreFactory>, Product<\Database\Factories\ProductFactory>>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    // --- قواعد التحقق ---
    /**
     * @var array<string, string>
     */
    protected $rules = [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'brand_id' => 'required|exists:brands,id',
        'category_id' => 'required|exists:categories,id',
    ];

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $rule) {
            $rules = explode('|', $rule);
            foreach ($rules as $singleRule) {
                if ($singleRule === 'required' && empty($this->$field)) {
                    $this->errors[$field] = ucfirst($field).' is required';
                } elseif ($singleRule === 'numeric' && isset($this->$field) && ! is_numeric($this->$field)) {
                    $this->errors[$field] = ucfirst($field).' must be numeric';
                } elseif (str_starts_with($singleRule, 'min:') && isset($this->$field)) {
                    $min = (float) substr($singleRule, 4);
                    if (is_numeric($this->$field) && $min > $this->$field) {
                        $this->errors[$field] = ucfirst($field).' must be at least '.$min;
                    }
                }
            }
        }

        return $this->errors === [];
    }

    /**
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    // --- Scopes ---
    /**
     * @param  Builder<Product<\Database\Factories\ProductFactory>>  $query
     * @return Builder<Product<\Database\Factories\ProductFactory>>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<Product<\Database\Factories\ProductFactory>>  $query
     * @return Builder<Product<\Database\Factories\ProductFactory>>
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    /**
     * @param  Builder<Product<\Database\Factories\ProductFactory>>  $query
     * @return Builder<Product<\Database\Factories\ProductFactory>>
     */
    public function scopeWithReviewsCount(Builder $query): Builder
    {
        return $query->withCount('reviews');
    }

    // --- طرق مساعدة ---
    public function getAverageRating(): float
    {
        $avg = $this->reviews()->avg('rating');

        return $avg ? (float) $avg : 0.0;
    }

    public function getTotalReviews(): int
    {
        return $this->reviews()->count();
    }

    public function isInWishlist(int $userId): bool
    {
        return $this->wishlists()->where('user_id', $userId)->exists();
    }

    public function getCurrentPrice(): float
    {
        $activeOffer = $this->priceOffers()->where('is_available', true)->latest()->first();

        return $activeOffer ? (float) $activeOffer->price : (float) $this->price;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, PriceOffer>
     */
    public function getPriceHistory(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->priceOffers()->orderBy('created_at', 'desc')->get();
    }

    protected static function booted(): void
    {
        static::deleting(function (self $product): void {
            // Ensure related records are fully removed during product deletion
            // to satisfy integrity expectations in tests and avoid orphan data
            $product->priceOffers()->forceDelete();
            $product->reviews()->forceDelete();
            $product->wishlists()->forceDelete();
            $product->priceAlerts()->forceDelete();
        });
    }
}
