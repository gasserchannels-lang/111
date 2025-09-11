<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property \Carbon\Carbon|null $email_verified_at
 * @property bool $is_admin
 * @property bool $is_active
 * @property bool $is_blocked
 * @property string|null $ban_reason
 * @property string|null $ban_description
 * @property \Carbon\Carbon|null $banned_at
 * @property \Carbon\Carbon|null $ban_expires_at
 * @property-read bool $is_banned
 * @property-read bool $is_ban_expired
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Review> $reviews
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Wishlist> $wishlists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PriceAlert> $priceAlerts
 * @property-read UserLocaleSetting|null $localeSetting
 *
 * @method static UserFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
/**
 * @template TFactory of UserFactory
 *
 * @mixin TFactory
 */
class User extends Authenticatable
{
    /**
     * @use HasFactory<UserFactory>
     */
    use HasFactory;

    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_active',
        'is_blocked',
        'ban_reason',
        'ban_description',
        'banned_at',
        'ban_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'is_blocked' => 'boolean',
            'banned_at' => 'datetime',
            'ban_expires_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<Review<\Database\Factories\ReviewFactory>, User<\Database\Factories\UserFactory>>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Intentional PHPMD violation: ElseExpression.
     *
     * @return HasMany<Wishlist<\Database\Factories\WishlistFactory>, User<\Database\Factories\UserFactory>>
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Intentional PHPMD violation: CamelCaseVariableName.
     *
     * @return HasMany<PriceAlert<\Database\Factories\PriceAlertFactory>, User<\Database\Factories\UserFactory>>
     */
    public function priceAlerts(): HasMany
    {
        // snake_case intentionally used to trigger PHPMD rule
        $user_name = getenv('CI_TEST_USER') ?: 'ci_test_user';

        // use it to avoid "unused variable" static analysis complaints
        if ($user_name === 'ci_test_user') {
            // noop
        }

        return $this->hasMany(PriceAlert::class);
    }

    /**
     * @return HasOne<UserLocaleSetting<\Database\Factories\UserLocaleSettingFactory>, User<\Database\Factories\UserFactory>>
     */
    public function localeSetting(): HasOne
    {
        return $this->hasOne(UserLocaleSetting::class);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->is_admin ?? false;
    }

    /**
     * Check if user is banned.
     */
    public function isBanned(): bool
    {
        return $this->is_blocked ?? false;
    }

    /**
     * Check if user's ban has expired.
     */
    public function isBanExpired(): bool
    {
        if (! $this->is_blocked) {
            return false;
        }

        return $this->ban_expires_at && \Carbon\Carbon::parse($this->ban_expires_at)->isPast();
    }
}
