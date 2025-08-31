<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'direction',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * العملات المرتبطة بهذه اللغة
     */
    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(Currency::class, 'language_currency')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    /**
     * العملة الافتراضية لهذه اللغة
     */
    public function defaultCurrency()
    {
        return $this->currencies()->wherePivot('is_default', true)->first();
    }

    /**
     * إعدادات المستخدمين لهذه اللغة
     */
    public function userLocaleSettings(): HasMany
    {
        return $this->hasMany(UserLocaleSetting::class);
    }

    /**
     * نطاق للغات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * نطاق للغات مرتبة حسب الترتيب
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * التحقق من كون اللغة من اليمين لليسار
     */
    public function isRtl(): bool
    {
        return $this->direction === 'rtl';
    }

    /**
     * الحصول على اللغة بالكود
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }
}
