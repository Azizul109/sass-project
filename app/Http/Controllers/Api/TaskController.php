<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function __construct(private TaskService $taskService)
    {
        // Remove the problematic middleware constructor authorization
    }

    public function index(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $tasks = $this->taskService->getProjectTasks(
            $project,
            $request->only(['status', 'priority'])
        );

        return response()->json($tasks);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'priority' => 'required|integer|min:1|max:5',
            'due_date' => 'nullable|date',
            'user_ids' => 'sometimes|array',
            'user_ids.*' => 'exists:users,id,company_id,' . $project->company_id,
        ]);

        $task = $this->taskService->createTask($project, $validated);

        // Assign users if provided
        if (isset($validated['user_ids'])) {
            $this->taskService->assignUsers($task, $validated['user_ids']);
        }

        return response()->json($task->load('users'), 201);
    }

    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task->load(['users', 'project']);
        return response()->json($task);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'priority' => 'sometimes|integer|min:1|max:5',
            'due_date' => 'nullable|date',
        ]);

        \Log::info("=== TASK UPDATE STARTED ===");
        \Log::info("Task ID: " . $task->id);
        \Log::info("Old Status: " . $task->status);
        \Log::info("New Status: " . ($validated['status'] ?? 'unchanged'));

        $oldStatus = $task->status;
        $task = $this->taskService->updateTask($task, $validated);

        // Check if task was just marked as completed
        if ($oldStatus !== 'completed' && $task->status === 'completed') {
            \Log::info("Task status changed to COMPLETED - triggering notifications");
            $this->sendTaskCompletedNotifications($task);
        } else {
            \Log::info("No status change to completed - skipping notifications");
        }

        \Log::info("=== TASK UPDATE COMPLETED ===");

        return response()->json($task->load(['users', 'project']));
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->taskService->deleteTask($task);
        return response()->json(['message' => 'Task deleted successfully']);
    }

    public function assignUsers(Request $request, Task $task): JsonResponse
    {
        $this->authorize('assignUsers', $task);

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id,company_id,' . $task->company_id,
        ]);

        $this->taskService->assignUsers($task, $validated['user_ids']);

        return response()->json(['message' => 'Users assigned successfully']);
    }

    public function projectTasks(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $tasks = $this->taskService->getProjectTasks($project, [])
            ->load('users');

        return response()->json($tasks);
    }

    /**
     * Send email notifications when a task is completed
     */
    private function sendTaskCompletedNotifications(Task $task): void
    {
        \Log::info("=== STARTING sendTaskCompletedNotifications ===");
        \Log::info("Task ID: " . $task->id);
        \Log::info("Task Title: " . $task->title);
        \Log::info("Task Status: " . $task->status);

        $assignedUsers = $task->users;

        \Log::info("Assigned users count: " . $assignedUsers->count());

        if ($assignedUsers->isEmpty()) {
            \Log::warning("No assigned users found for task " . $task->id);
            \Log::info("=== END sendTaskCompletedNotifications - NO USERS ===");
            return;
        }

        foreach ($assignedUsers as $user) {
            \Log::info("Processing user: " . $user->email);

            try {
                \Log::info("Attempting to send email to: " . $user->email);

                // Method 1: Send directly (synchronously) for debugging
                \Illuminate\Support\Facades\Mail::to($user->email)
                    ->send(new \App\Mail\TaskCompletedNotification($task, $user));

                \Log::info("✓ Email sent successfully to: " . $user->email);

            } catch (\Exception $e) {
                \Log::error("✗ Failed to send email to " . $user->email . ": " . $e->getMessage());
                \Log::error("Error in: " . $e->getFile() . ":" . $e->getLine());
                \Log::error("Stack trace: " . $e->getTraceAsString());
            }
        }

        \Log::info("=== COMPLETED sendTaskCompletedNotifications ===");
    }
}