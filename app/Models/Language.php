<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @template TFactory of \Illuminate\Database\Eloquent\Factories\Factory
 */
class Language extends Model
{
    /** @use HasFactory<\App\Models\Language> */
    use HasFactory;

    /** @var list<string> */
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Currency, \App\Models\Language, \Illuminate\Database\Eloquent\Relations\Pivot, 'pivot'>
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
    public function defaultCurrency(): ?\App\Models\Currency
    {
        return $this->currencies()->wherePivot('is_default', true)->first();
    }

    /**
     * إعدادات المستخدمين لهذه اللغة
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\UserLocaleSetting, \App\Models\Language>
     */
    public function userLocaleSettings(): HasMany
    {
        return $this->hasMany(UserLocaleSetting::class);
    }

    /**
     * نطاق للغات النشطة فقط
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Language>  $queryBuilder
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $queryBuilder): void
    {
        $queryBuilder->where('is_active', true);
    }

    /**
     * نطاق للغات مرتبة حسب الترتيب
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Language>  $queryBuilder
     */
    public function scopeOrdered(\Illuminate\Database\Eloquent\Builder $queryBuilder): void
    {
        $queryBuilder->orderBy('sort_order')->orderBy('name');
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
