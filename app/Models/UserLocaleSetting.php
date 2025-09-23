<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserLocaleSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $session_id
 * @property int $language_id
 * @property int $currency_id
 * @property string|null $ip_address
 * @property string|null $country_code
 * @property-read User|null $user
 * @property-read Language $language
 * @property-read Currency $currency
 *
 * @method static UserLocaleSettingFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
class UserLocaleSetting extends Model
{
    /** @phpstan-ignore-next-line */
    use HasFactory;

    /**
     * @var class-string<\Illuminate\Database\Eloquent\Factories\Factory<UserLocaleSetting>>
     */
    protected static $factory = \Database\Factories\UserLocaleSettingFactory::class;

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
     * المستخدم المرتبط بهذا الإعداد.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * اللغة المحددة.
     *
     * @return BelongsTo<Language, $this>
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * العملة المحددة.
     *
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * البحث عن إعدادات المستخدم.
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
     * إنشاء أو تحديث إعدادات المستخدم.
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
