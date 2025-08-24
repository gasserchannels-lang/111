<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'currency_language');
    }
}
