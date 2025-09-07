<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @template TFactory of \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 * @mixin TFactory
 */
class Brand extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        // ✅ الحقل الجديد ليتوافق مع الـ migration القادم
        'is_active',
    ];

    /**
     * Get the products for the brand.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Product, $this>
     */
    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Product::class, 'brand_id');
    }
}