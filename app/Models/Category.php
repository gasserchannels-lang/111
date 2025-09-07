<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int|null $parent_id
 * @property int $level
 * @property bool $is_active
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 *
 * @method static CategoryFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
/**
 * @template TFactory of \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 *
 * @mixin TFactory
 */
class Category extends Model
{
    use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>>;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'level',
        'is_active',
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
}
