<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantScope
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $companyId = Auth::user()->company_id;
            
            // Apply global scopes for tenant isolation
            \App\Models\Project::addGlobalScope('tenant', function ($builder) use ($companyId) {
                $builder->where('company_id', $companyId);
            });
            
            \App\Models\Task::addGlobalScope('tenant', function ($builder) use ($companyId) {
                $builder->where('company_id', $companyId);
            });
            
            \App\Models\ScrapedData::addGlobalScope('tenant', function ($builder) use ($companyId) {
                $builder->where('company_id', $companyId);
            });
        }

        return $next($request);
    }
}