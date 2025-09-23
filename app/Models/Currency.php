<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CurrencyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $symbol
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Store> $stores
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Language> $languages
 *
 * @method static CurrencyFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
class Currency extends Model
{
    /** @phpstan-ignore-next-line */
    use HasFactory;

    /**
     * @var class-string<\Illuminate\Database\Eloquent\Factories\Factory<Currency>>
     */
    protected static $factory = \Database\Factories\CurrencyFactory::class;

    /**
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * @return HasMany<Store, $this>
     */
    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    /**
     * @return BelongsToMany<Language, $this>
     */
    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'currency_language');
    }
}
