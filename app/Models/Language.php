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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Currency<Database\Factories\CurrencyFactory>> $currencies
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserLocaleSetting<Database\Factories\UserLocaleSettingFactory>> $userLocaleSettings
 *
 * @method static LanguageFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
/**
 * @template TFactory of LanguageFactory
 *
 * @mixin TFactory
 */
class Language extends Model
{
    /**
     * @use HasFactory<LanguageFactory>
     */
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
     * العملات المرتبطة بهذه اللغة.
     *
     * @return BelongsToMany<Currency<Database\Factories\CurrencyFactory>, Language<Database\Factories\LanguageFactory>>
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
    public function defaultCurrency(): ?Currency
    {
        return $this->currencies()->wherePivot('is_default', true)->first();
    }

    /**
     * إعدادات المستخدمين لهذه اللغة.
     *
     * @return HasMany<UserLocaleSetting<Database\Factories\UserLocaleSettingFactory>, Language<Database\Factories\LanguageFactory>>
     */
    public function userLocaleSettings(): HasMany
    {
        return $this->hasMany(UserLocaleSetting::class);
    }

    /**
     * نطاق للغات النشطة فقط.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Language>  $queryBuilder
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $queryBuilder): void
    {
        $queryBuilder->where('is_active', true);
    }

    /**
     * نطاق للغات مرتبة حسب الترتيب.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Language>  $queryBuilder
     */
    public function scopeOrdered(\Illuminate\Database\Eloquent\Builder $queryBuilder): void
    {
        $queryBuilder->orderBy('sort_order')->orderBy('name');
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
