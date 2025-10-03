<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view tasks
        return $user->isEmployee();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // User must belong to the same company as the task
        // AND must be an employee OR assigned to the task
        return $user->company_id === $task->company_id && 
               ($user->isEmployee() || $task->users->contains($user->id));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only managers and owners can create tasks
        return $user->isManager();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // User must belong to the same company as the task
        // AND must be a manager OR assigned to the task
        return $user->company_id === $task->company_id && 
               ($user->isManager() || $task->users->contains($user->id));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Only managers can delete tasks
        return $user->company_id === $task->company_id && $user->isManager();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $user->company_id === $task->company_id && $user->isManager();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $user->company_id === $task->company_id && $user->isOwner();
    }

    /**
     * Determine whether the user can assign users to the task.
     */
    public function assignUsers(User $user, Task $task): bool
    {
        // Only managers can assign users to tasks
        return $user->company_id === $task->company_id && $user->isManager();
    }

    /**
     * Determine whether the user can change task status.
     */
    public function changeStatus(User $user, Task $task): bool
    {
        // Managers OR assigned users can change task status
        return $user->company_id === $task->company_id && 
               ($user->isManager() || $task->users->contains($user->id));
    }

    /**
     * Determine whether the user can view task reports.
     */
    public function viewReports(User $user, Task $task): bool
    {
        return $user->company_id === $task->company_id && $user->isManager();
    }
}