<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:4',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * اللغات المرتبطة بهذه العملة
     */
    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'language_currency')
                    ->withPivot('is_default')
                    ->withTimestamps();
    }

    /**
     * إعدادات المستخدمين لهذه العملة
     */
    public function userLocaleSettings(): HasMany
    {
        return $this->hasMany(UserLocaleSetting::class);
    }

    /**
     * نطاق للعملات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * نطاق للعملات مرتبة حسب الترتيب
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * تحويل المبلغ من الدولار إلى هذه العملة
     */
    public function convertFromUSD(float $amount): float
    {
        return $amount * $this->exchange_rate;
    }

    /**
     * تحويل المبلغ من هذه العملة إلى الدولار
     */
    public function convertToUSD(float $amount): float
    {
        return $amount / $this->exchange_rate;
    }

    /**
     * تنسيق المبلغ بعملة هذه العملة
     */
    public function format(float $amount): string
    {
        return $this->symbol . ' ' . number_format($amount, 2);
    }

    /**
     * الحصول على العملة بالكود
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }
}
