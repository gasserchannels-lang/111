<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $is_active
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
    /**
     * @use HasFactory<BrandFactory>
     */
    use HasFactory;

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
     * Get the products for the brand.
     *
     * @return HasMany<Product, Brand>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'brand_id');
    }
}
