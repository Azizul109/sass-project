<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only managers can view user lists
        return $user->isManager();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Users can view their own profile
        // Managers can view any user in their company
        return $user->id === $model->id || 
               ($user->company_id === $model->company_id && $user->isManager());
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only managers can create new users
        return $user->isManager();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Users can update their own profile
        // Managers can update any user in their company
        return $user->id === $model->id || 
               ($user->company_id === $model->company_id && $user->isManager());
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        // Only managers can delete users (except themselves)
        return $user->id !== $model->id && 
               $user->company_id === $model->company_id && 
               $user->isManager();
    }

    /**
     * Determine whether the user can change user roles.
     */
    public function changeRole(User $user, User $model): bool
    {
        // Only owners can change roles (and cannot change their own role)
        return $user->id !== $model->id && 
               $user->company_id === $model->company_id && 
               $user->isOwner();
    }
}