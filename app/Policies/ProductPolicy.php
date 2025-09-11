<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     */
    public function viewAny(User $user): bool
    {
        return true; // يمكن للجميع عرض المنتجات
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     * @param  Product<\Database\Factories\ProductFactory>  $product
     */
    public function view(User $user, Product $product): bool
    {
        return $product->is_active || $user->isAdmin();
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
     * @param  Product<\Database\Factories\ProductFactory>  $product
     */
    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     * @param  Product<\Database\Factories\ProductFactory>  $product
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     * @param  Product<\Database\Factories\ProductFactory>  $product
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     * @param  Product<\Database\Factories\ProductFactory>  $product
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }
}
