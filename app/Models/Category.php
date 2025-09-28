<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\ValidatesModel;
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
 * @property Category|null $parent
 * @property \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property \Illuminate\Database\Eloquent\Collection<int, Product> $products
 *
 * @method static CategoryFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
class Category extends Model
{
    /** @phpstan-ignore-next-line */
    use HasFactory;

    use ValidatesModel;

    /**
     * @var class-string<\Illuminate\Database\Eloquent\Factories\Factory<Category>>
     */
    protected static $factory = \Database\Factories\CategoryFactory::class;

    use SoftDeletes;

    /**
     * Create a new factory instance for the model.
     *
     * @param  array<string, mixed>  $state
     * @return \Illuminate\Database\Eloquent\Factories\Factory<Category>
     */
    public static function factory(?int $count = null, array $state = []): \Illuminate\Database\Eloquent\Factories\Factory
    {
        $factory = static::newFactory();
        if ($factory && $count !== null) {
            $factory = $factory->count($count);
        }

        return $factory ? $factory->state($state)->connection('testing') : \Database\Factories\CategoryFactory::new();
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
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Children categories relationship.
     *
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Products relationship.
     *
     * @return HasMany<Product, $this>
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
                $category->load('parent');
                $category->level = $category->parent ? $category->parent->level + 1 : 0;
            }
        });
    }
}
