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
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
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
        ];
    }

    /**
     * @return HasMany<Review, User>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Intentional PHPMD violation: ElseExpression
     *
     * @return HasMany<Wishlist, User>
     */
    public function wishlists(): HasMany
    {
        // runtime condition so PHPStan doesn't treat it as always-true
        if (random_int(0, 1) === 1) {
            return $this->hasMany(Wishlist::class);
        } else {
            return $this->hasMany(Wishlist::class);
        }
    }

    /**
     * Intentional PHPMD violation: CamelCaseVariableName
     *
     * @return HasMany<PriceAlert, User>
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
     * @return HasOne<UserLocaleSetting, User>
     */
    public function localeSetting(): HasOne
    {
        return $this->hasOne(UserLocaleSetting::class);
    }
}
