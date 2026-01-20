<?php

namespace App\Policies;

use App\Models\Refinance;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RefinancePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_refinances');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Refinance $refinance): bool
    {
        return $user->hasPermissionTo('view_refinance');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_refinance');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Refinance $refinance): bool
    {
        return $user->hasPermissionTo('update_refinance');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Refinance $refinance): bool
    {
        return $user->hasPermissionTo('delete_refinance');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Refinance $refinance): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Refinance $refinance): bool
    {
        return false;
    }
}
