<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CategoryFactory;
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
 * @property string|null $description
 * @property int|null $parent_id
 * @property int $level
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 *
 * @method static CategoryFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 *
 * @template TFactory of CategoryFactory
 *
 * @mixin TFactory
 */
class Category extends Model
{
    /** @use HasFactory<TFactory> */
    use HasFactory {
        factory as baseFactory;
    }

    use SoftDeletes;

    /**
     * Create a new factory instance for the model.
     *
     * @param  array<string, mixed>  $state
     */
    public static function factory(?int $count = null, array $state = []): CategoryFactory
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
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'level',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
    ];

    /**
     * The attributes that should be validated.
     *
     * @var array<string, string>
     */
    protected $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'nullable|string|max:255|unique:categories,slug',
        'description' => 'nullable|string|max:1000',
        'parent_id' => 'nullable|exists:categories,id',
        'level' => 'integer|min:0',
        'is_active' => 'boolean',
    ];

    /**
     * Parent category relationship.
     *
     * @return BelongsTo<Category, Category>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Children categories relationship.
     *
     * @return HasMany<Category, Category>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Products relationship.
     *
     * @return HasMany<Product, Category>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Scope a query to only include active categories.
     *
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search categories by name.
     *
     * @param  Builder<Category>  $query
     * @return Builder<Category>
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

        static::creating(function ($category): void {
            if (empty($category->slug)) {
                $category->slug = \Str::slug($category->name);
            }

            if (empty($category->level)) {
                $category->level = $category->parent ? $category->parent->level + 1 : 0;
            }
        });

        static::updating(function ($category): void {
            if ($category->isDirty('name')) {
                $category->slug = \Str::slug($category->name);
            }

            if ($category->isDirty('parent_id')) {
                $category->level = $category->parent ? $category->parent->level + 1 : 0;
            }
        });
    }
}
