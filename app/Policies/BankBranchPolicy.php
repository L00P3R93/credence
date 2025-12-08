<?php

namespace App\Policies;

use App\Models\BankBranch;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BankBranchPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_branches');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BankBranch $bankBranch): bool
    {
        return $user->hasPermissionTo('view_branch');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_branch');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BankBranch $bankBranch): bool
    {
        return $user->hasPermissionTo('update_branch');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BankBranch $bankBranch): bool
    {
        return $user->hasPermissionTo('delete_branch');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BankBranch $bankBranch): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BankBranch $bankBranch): bool
    {
        return false;
    }
}
