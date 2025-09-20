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
/**
 * @template TFactory of CurrencyFactory
 *
 * @mixin TFactory
 */
class Currency extends Model
{
    /**
     * @use HasFactory<CurrencyFactory>
     */
    use HasFactory;

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Store>
     */
    public function stores(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Store::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Language>
     */
    public function languages(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'currency_language');
    }
}
