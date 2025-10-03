<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only owners can view company list (in multi-tenant context)
        return $user->isOwner();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Company $company): bool
    {
        // User can only view their own company
        return $user->company_id === $company->id && $user->isOwner();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Typically only system admins can create companies
        // For this app, registration handles company creation
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Company $company): bool
    {
        // Only owners can update company details
        return $user->company_id === $company->id && $user->isOwner();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Company $company): bool
    {
        // Only owners can delete their company
        return $user->company_id === $company->id && $user->isOwner();
    }

    /**
     * Determine whether the user can manage company users.
     */
    public function manageUsers(User $user, Company $company): bool
    {
        // Only owners and managers can manage users
        return $user->company_id === $company->id && $user->isManager();
    }

    /**
     * Determine whether the user can view company reports.
     */
    public function viewReports(User $user, Company $company): bool
    {
        return $user->company_id === $company->id && $user->isManager();
    }

    /**
     * Determine whether the user can manage company settings.
     */
    public function manageSettings(User $user, Company $company): bool
    {
        return $user->company_id === $company->id && $user->isOwner();
    }
}