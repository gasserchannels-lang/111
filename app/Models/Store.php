<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\StoreFactory;
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
 * @property string|null $description
 * @property string|null $logo_url
 * @property string|null $website_url
 * @property string|null $country_code
 * @property array|null $supported_countries
 * @property bool $is_active
 * @property int $priority
 * @property string|null $affiliate_base_url
 * @property string|null $affiliate_code
 * @property array|null $api_config
 * @property int|null $currency_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PriceOffer> $priceOffers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 * @property-read Currency|null $currency
 *
 * @method static StoreFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
/**
 * @template TFactory of StoreFactory
 *
 * @mixin TFactory
 */
class Store extends Model
{
    /**
     * @use HasFactory<StoreFactory>
     */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo_url',
        'website_url',
        'country_code',
        'supported_countries',
        'is_active',
        'priority',
        'affiliate_base_url',
        'affiliate_code',
        'api_config',
        'currency_id',
    ];

    protected $casts = [
        'api_config' => 'array',
        'supported_countries' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * The attributes that should be validated.
     *
     * @var array<string, string>
     */
    protected $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'nullable|string|max:255|unique:stores,slug',
        'description' => 'nullable|string|max:1000',
        'logo_url' => 'nullable|url|max:500',
        'website_url' => 'nullable|url|max:500',
        'country_code' => 'nullable|string|max:2',
        'supported_countries' => 'nullable|array',
        'is_active' => 'boolean',
        'priority' => 'integer|min:0',
        'affiliate_base_url' => 'nullable|url|max:500',
        'affiliate_code' => 'nullable|string|max:100',
        'api_config' => 'nullable|array',
        'currency_id' => 'nullable|exists:currencies,id',
    ];

    /**
     * @return HasMany<PriceOffer, Store>
     */
    public function priceOffers(): HasMany
    {
        return $this->hasMany(PriceOffer::class);
    }

    /**
     * @return HasMany<Product, Store>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'store_id');
    }

    /**
     * @return BelongsTo<Currency, Store>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Scope a query to only include active stores.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search stores by name.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    /**
     * Get validation rules for the model.
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
     */
    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($store) {
            if (empty($store->slug)) {
                $store->slug = \Str::slug($store->name);
            }
        });

        static::updating(function ($store) {
            if ($store->isDirty('name') && empty($store->slug)) {
                $store->slug = \Str::slug($store->name);
            }
        });
    }

    public function generateAffiliateUrl(string $productUrl): string
    {
        if (empty($this->affiliate_base_url) || empty($this->affiliate_code)) {
            return $productUrl;
        }

        $affiliateCode = (string) $this->affiliate_code;
        $affiliateBaseUrl = (string) $this->affiliate_base_url;

        $affiliateUrl = str_replace('{AFFILIATE_CODE}', $affiliateCode, $affiliateBaseUrl);
        $affiliateUrl = str_replace('{URL}', urlencode($productUrl), $affiliateUrl);

        return $affiliateUrl;
    }
}
