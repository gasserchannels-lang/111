<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property Carbon|null $email_verified_at
 * @property bool $is_admin
 * @property bool $is_active
 * @property bool $is_blocked
 * @property string|null $ban_reason
 * @property string|null $ban_description
 * @property Carbon|null $banned_at
 * @property Carbon|null $ban_expires_at
 * @property string|null $session_id
 * @property-read bool $is_banned
 * @property-read bool $is_ban_expired
 * @property-read Collection<int, Review> $reviews
 * @property-read Collection<int, Wishlist> $wishlists
 * @property-read Collection<int, PriceAlert> $priceAlerts
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
    use HasApiTokens, HasFactory, Notifiable;

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
        'session_id',
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Review>
     */
    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Intentional PHPMD violation: ElseExpression.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Wishlist>
     */
    public function wishlists(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Intentional PHPMD violation: CamelCaseVariableName.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<PriceAlert>
     */
    public function priceAlerts(): \Illuminate\Database\Eloquent\Relations\HasMany
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<UserLocaleSetting>
     */
    public function localeSetting(): \Illuminate\Database\Eloquent\Relations\HasOne
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

        return $this->ban_expires_at && Carbon::parse($this->ban_expires_at)->isPast();
    }
}
