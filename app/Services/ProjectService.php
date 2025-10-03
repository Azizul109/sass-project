<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Company;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function getCompanyProjects(Company $company, array $filters = []): LengthAwarePaginator
    {
        $query = Project::where('company_id', $company->id);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->withCount('tasks')->latest()->paginate(10);
    }

    public function createProject(Company $company, array $data): Project
    {
        return Project::create([
            ...$data,
            'company_id' => $company->id,
        ]);
    }

    public function updateProject(Project $project, array $data): Project
    {
        $project->update($data);
        return $project->fresh();
    }

    public function deleteProject(Project $project): void
    {
        $project->delete();
    }
}