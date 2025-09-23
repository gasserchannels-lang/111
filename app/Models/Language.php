<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LanguageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $native_name
 * @property string $direction
 * @property bool $is_active
 * @property int $sort_order
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Currency> $currencies
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserLocaleSetting> $userLocaleSettings
 *
 * @method static LanguageFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
class Language extends Model
{
    /** @phpstan-ignore-next-line */
    use HasFactory;

    /**
     * @var class-string<\Illuminate\Database\Eloquent\Factories\Factory<Language>>
     */
    protected static $factory = \Database\Factories\LanguageFactory::class;

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
     * العملات المرتبطة بهذه اللغة.
     *
     * @return BelongsToMany<Currency, $this>
     */
    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(Currency::class, 'language_currency')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    /**
     * العملة الافتراضية لهذه اللغة.
     */
    public function defaultCurrency(): ?\App\Models\Currency
    {
        /** @var \App\Models\Currency|null $currency */
        $currency = $this->currencies()->wherePivot('is_default', true)->first();

        return $currency;
    }

    /**
     * إعدادات المستخدمين لهذه اللغة.
     *
     * @return HasMany<UserLocaleSetting, $this>
     */
    public function userLocaleSettings(): HasMany
    {
        return $this->hasMany(UserLocaleSetting::class);
    }

    /**
     * نطاق للغات النشطة فقط.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Language>  $queryBuilder
     * @return \Illuminate\Database\Eloquent\Builder<Language>
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $queryBuilder): \Illuminate\Database\Eloquent\Builder
    {
        return $queryBuilder->where('is_active', true);
    }

    /**
     * نطاق للغات مرتبة حسب الترتيب.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Language>  $queryBuilder
     * @return \Illuminate\Database\Eloquent\Builder<Language>
     */
    public function scopeOrdered(\Illuminate\Database\Eloquent\Builder $queryBuilder): \Illuminate\Database\Eloquent\Builder
    {
        return $queryBuilder->orderBy('sort_order')->orderBy('name');
    }

    /**
     * التحقق من كون اللغة من اليمين لليسار.
     */
    public function isRtl(): bool
    {
        return $this->direction === 'rtl';
    }

    /**
     * الحصول على اللغة بالكود.
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }
}
