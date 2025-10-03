<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Project::class => \App\Policies\ProjectPolicy::class,
        \App\Models\Task::class => \App\Policies\TaskPolicy::class,
        \App\Models\Company::class => \App\Policies\CompanyPolicy::class,
        \App\Models\ScrapedData::class => \App\Policies\ScrapedDataPolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define additional gates for specific permissions
        Gate::define('view-dashboard', function ($user) {
            return $user->isEmployee();
        });

        Gate::define('manage-users', function ($user) {
            return $user->isManager();
        });

        Gate::define('view-reports', function ($user) {
            return $user->isManager();
        });

        Gate::define('export-data', function ($user) {
            return $user->isEmployee();
        });

        Gate::define('configure-system', function ($user) {
            return $user->isOwner();
        });
    }
}