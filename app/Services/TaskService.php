<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    public function getProjectTasks(Project $project, array $filters = []): LengthAwarePaginator
    {
        $query = Task::where('project_id', $project->id)
                    ->with(['users', 'project']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return $query->latest()->paginate(15);
    }

    public function createTask(Project $project, array $data): Task
    {
        return Task::create([
            ...$data,
            'company_id' => $project->company_id,
            'project_id' => $project->id,
        ]);
    }

    public function updateTask(Task $task, array $data): Task
    {
        $oldStatus = $task->status;
        $task->update($data);

        // Check if task was just completed
        if ($oldStatus !== 'completed' && $task->status === 'completed') {
            // The email notification will be handled by the controller
            // This separation keeps the service focused on data operations
        }

        return $task->fresh(['users', 'project']);
    }

    public function deleteTask(Task $task): void
    {
        $task->delete();
    }

    public function assignUsers(Task $task, array $userIds): void
    {
        $task->users()->sync($userIds);
    }

    public function getCompanyCompletedTasks(int $companyId)
    {
        return Task::where('company_id', $companyId)
                  ->where('status', 'completed')
                  ->with(['project.company'])
                  ->get();
    }
}