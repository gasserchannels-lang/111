<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    // ✅ الخطوة 1: إضافة الـ Trait الأساسي لربط الموديل بالـ Factory
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        // ✅ الخطوة 2: إضافة الحقل الجديد ليتوافق مع الـ migration القادم
        'is_active',
    ];

    /**
     * Products relationship.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'brand_id');
    }
}
