<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property float $price
 * @property string|null $image
 * @property bool $is_active
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
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'image',
        'is_active',
        'category_id',
        'brand_id',
        'store_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $errors = [];

    // --- العلاقات ---
    /**
     * @return BelongsTo<Brand, Product>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return BelongsTo<Category, Product>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<PriceAlert, Product>
     */
    public function priceAlerts(): HasMany
    {
        return $this->hasMany(PriceAlert::class);
    }

    /**
     * @return HasMany<Review, Product>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return HasMany<Wishlist, Product>
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * @return HasMany<PriceOffer, Product>
     */
    public function priceOffers(): HasMany
    {
        return $this->hasMany(PriceOffer::class);
    }

    /**
     * @return BelongsTo<Store, Product>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    // --- قواعد التحقق ---
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
                    $this->errors[$field] = ucfirst($field) . ' is required';
                } elseif ($singleRule === 'numeric' && isset($this->$field) && !is_numeric($this->$field)) {
                    $this->errors[$field] = ucfirst($field) . ' must be numeric';
                } elseif (str_starts_with($singleRule, 'min:') && isset($this->$field)) {
                    $min = (float) substr($singleRule, 4);
                    if (is_numeric($this->$field) && $this->$field < $min) {
                        $this->errors[$field] = ucfirst($field) . ' must be at least ' . $min;
                    }
                }
            }
        }
        
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    // --- Scopes ---
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    public function scopeWithReviewsCount(Builder $query): Builder
    {
        return $query->withCount('reviews');
    }

    // --- طرق مساعدة ---
    public function getAverageRating(): float
    {
        return $this->reviews()->avg('rating') ?? 0.0;
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
        $activeOffer = $this->priceOffers()->where('is_active', true)->latest()->first();
        return $activeOffer ? $activeOffer->price : $this->price;
    }

    public function getPriceHistory()
    {
        return $this->priceOffers()->orderBy('created_at', 'desc')->get();
    }
}
