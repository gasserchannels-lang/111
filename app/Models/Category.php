<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @template TFactory of \Illuminate\Database\Eloquent\Factories\Factory
 */
class Category extends Model
{
    // ✅ الخطوة 1: إضافة الـ Trait الأساسي لربط الموديل بالـ Factory
    /** @use HasFactory<\App\Models\Category> */
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'level',
        // ✅ الخطوة 2: إضافة الحقل الجديد ليتوافق مع الـ migration
        'is_active',
    ];

    /**
     * Parent category relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Category, \App\Models\Category>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Children categories relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Category, \App\Models\Category>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Products relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Product, \App\Models\Category>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
