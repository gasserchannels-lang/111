<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $logo_url
 * @property string|null $website_url
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 *
 * @method static BrandFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
/**
 * @template TFactory of BrandFactory
 *
 * @mixin TFactory
 */
class Brand extends Model
{
    /** @use HasFactory<TFactory> */
    use HasFactory {
        factory as baseFactory;
    }

    use SoftDeletes;

    /**
     * Create a new factory instance for the model.
     *
     * @param  int|null  $count
     * @param  array<string, mixed>  $state
     * @return BrandFactory
     */
    public static function factory($count = null, $state = [])
    {
        return static::baseFactory($count, $state)->connection('testing');
    }

    /**
     * Validation errors.
     *
     * @var array<string, mixed>
     */
    protected array $errors = [];

    /**
     * Mass assignable attributes.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo_url',
        'website_url',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The attributes that should be validated.
     *
     * @var array<string, string>
     */
    protected $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'nullable|string|max:255|unique:brands,slug',
        'description' => 'nullable|string|max:1000',
        'logo_url' => 'nullable|url|max:500',
        'website_url' => 'nullable|url|max:500',
        'is_active' => 'boolean',
    ];

    /**
     * Get the products for the brand.
     *
     * @return HasMany<Product, Brand>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

    /**
     * Scope a query to only include active brands.
     *
     * @param  Builder<Brand>  $query
     * @return Builder<Brand>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search brands by name.
     *
     * @param  Builder<Brand>  $query
     * @return Builder<Brand>
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    /**
     * Get validation rules for the model.
     */
    /**
     * @return array<string, string>
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
    /**
     * @return array<string, string>
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

        static::creating(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = \Str::slug($brand->name);
            }
        });

        static::updating(function ($brand) {
            if ($brand->isDirty('name') && empty($brand->slug)) {
                $brand->slug = \Str::slug($brand->name);
            }
        });
    }
}
