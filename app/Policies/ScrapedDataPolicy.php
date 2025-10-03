<?php

namespace App\Policies;

use App\Models\ScrapedData;
use App\Models\User;

class ScrapedDataPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view scraped data
        return $user->isEmployee();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ScrapedData $scrapedData): bool
    {
        // User must belong to the same company as the scraped data
        return $user->company_id === $scrapedData->company_id && $user->isEmployee();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only managers can initiate scraping
        return $user->isManager();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ScrapedData $scrapedData): bool
    {
        // Only managers can update scraped data
        return $user->company_id === $scrapedData->company_id && $user->isManager();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ScrapedData $scrapedData): bool
    {
        // Only managers can delete scraped data
        return $user->company_id === $scrapedData->company_id && $user->isManager();
    }

    /**
     * Determine whether the user can export scraped data.
     */
    public function export(User $user): bool
    {
        // All authenticated users can export data
        return $user->isEmployee();
    }

    /**
     * Determine whether the user can initiate scraping.
     */
    public function scrape(User $user): bool
    {
        // Only managers can initiate scraping
        return $user->isManager();
    }
}