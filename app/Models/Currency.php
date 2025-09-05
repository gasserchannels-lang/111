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
class Currency extends Model
{
    /** @use HasFactory<\App\Models\Currency> */
    use HasFactory;

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Store, \App\Models\Currency>
     */
    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Language, \App\Models\Currency, \Illuminate\Database\Eloquent\Relations\Pivot, 'pivot'>
     */
    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'currency_language');
    }
}
