<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class UserLocaleSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'language_id',
        'currency_id',
        'ip_address',
        'country_code',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'language_id' => 'integer',
        'currency_id' => 'integer',
    ];

    /**
     * المستخدم المرتبط بهذا الإعداد
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\UserLocaleSetting>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * اللغة المحددة
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Language, \App\Models\UserLocaleSetting>
     */
    public function language(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Language::class);
    }

    /**
     * العملة المحددة
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Currency, \App\Models\UserLocaleSetting>
     */
    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Currency::class);
    }

    /**
     * البحث عن إعدادات المستخدم
     */
    public static function findForUser(?int $userId, ?string $sessionId): ?self
    {
        $query = static::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if (! $userId && $sessionId) {
            $query->where('session_id', $sessionId);
        }

        if (! $userId && ! $sessionId) {
            return null;
        }

        return $query->latest()->first();
    }

    /**
     * إنشاء أو تحديث إعدادات المستخدم
     */
    public static function updateOrCreateForUser(
        ?int $userId,
        ?string $sessionId,
        int $languageId,
        int $currencyId,
        ?string $ipAddress = null,
        ?string $countryCode = null
    ): self {
        $attributes = [
            'language_id' => $languageId,
            'currency_id' => $currencyId,
            'ip_address' => $ipAddress,
            'country_code' => $countryCode,
        ];

        if ($userId) {
            return static::updateOrCreate(
                ['user_id' => $userId],
                $attributes
            );
        }

        if ($sessionId) {
            return static::updateOrCreate(
                ['session_id' => $sessionId],
                $attributes
            );
        }

        // Fallback case - this should not happen based on the calling logic
        throw new InvalidArgumentException('Either userId or sessionId must be provided');
    }
}
