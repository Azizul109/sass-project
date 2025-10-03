<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view projects
        return $user->isEmployee();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        // User must belong to the same company AND be at least an employee
        return $user->company_id === $project->company_id && $user->isEmployee();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only managers and owners can create projects
        return $user->isManager();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        // User must belong to the same company AND be a manager/owner
        return $user->company_id === $project->company_id && $user->isManager();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        // User must belong to the same company AND be an owner
        return $user->company_id === $project->company_id && $user->isOwner();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id && $user->isManager();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id && $user->isOwner();
    }
}