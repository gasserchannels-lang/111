<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     * @param  User<\Database\Factories\UserFactory>  $model
     */
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     * @param  User<\Database\Factories\UserFactory>  $model
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     * @param  User<\Database\Factories\UserFactory>  $model
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     * @param  User<\Database\Factories\UserFactory>  $model
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     * @param  User<\Database\Factories\UserFactory>  $model
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }
}
