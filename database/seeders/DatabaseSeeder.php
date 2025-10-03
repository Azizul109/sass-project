<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'domain' => 'test-company.local'
        ]);

        $owner = User::create([
            'company_id' => $company->id,
            'name' => 'Company Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'role' => 'owner'
        ]);

        $manager = User::create([
            'company_id' => $company->id,
            'name' => 'Project Manager',
            'email' => 'manager@test.com',
            'password' => bcrypt('password'),
            'role' => 'manager'
        ]);

        $employee = User::create([
            'company_id' => $company->id,
            'name' => 'Team Member',
            'email' => 'employee@test.com',
            'password' => bcrypt('password'),
            'role' => 'employee'
        ]);

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Website Redesign',
            'description' => 'Complete website redesign project',
            'status' => 'active'
        ]);

        Task::create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'title' => 'Design Homepage',
            'description' => 'Create new homepage design',
            'status' => 'in_progress'
        ])->users()->attach([$manager->id, $employee->id]);
    }
}