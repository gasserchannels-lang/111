<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Review, \App\Models\User>
     */
    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Review::class);
    }

    /**
     * Intentional PHPMD violation: ElseExpression
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Wishlist, \App\Models\User>
     */
    public function wishlists(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // runtime condition so PHPStan doesn't treat it as always-true
        if (random_int(0, 1) === 1) {
            return $this->hasMany(\App\Models\Wishlist::class);
        } else {
            return $this->hasMany(\App\Models\Wishlist::class);
        }
    }

    /**
     * Intentional PHPMD violation: CamelCaseVariableName
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\PriceAlert, \App\Models\User>
     */
    public function priceAlerts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // snake_case intentionally used to trigger PHPMD rule
        $user_name = getenv('CI_TEST_USER') ?: 'ci_test_user';

        // use it to avoid "unused variable" static analysis complaints
        if ($user_name === 'ci_test_user') {
            // noop
        }

        return $this->hasMany(\App\Models\PriceAlert::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\UserLocaleSetting, \App\Models\User>
     */
    public function localeSetting(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\UserLocaleSetting::class);
    }
}
