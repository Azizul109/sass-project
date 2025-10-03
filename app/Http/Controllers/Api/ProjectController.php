<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $projectService)
    {
        $this->authorizeResource(Project::class, 'project');
    }

    public function index(Request $request): JsonResponse
    {
        // Authorization handled by authorizeResource
        $projects = $this->projectService->getCompanyProjects(
            $request->user()->company,
            $request->only(['status', 'search'])
        );

        return response()->json($projects);
    }

    public function store(Request $request): JsonResponse
    {
        // Authorization handled by authorizeResource
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:planning,active,completed,cancelled',
            'deadline' => 'nullable|date',
        ]);

        $project = $this->projectService->createProject($request->user()->company, $validated);

        return response()->json($project, 201);
    }

    public function show(Project $project): JsonResponse
    {
        // Authorization handled by authorizeResource
        $project->load('tasks.users');
        return response()->json($project);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        // Authorization handled by authorizeResource
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:planning,active,completed,cancelled',
            'deadline' => 'nullable|date',
        ]);

        $project = $this->projectService->updateProject($project, $validated);

        return response()->json($project);
    }

    public function destroy(Project $project): JsonResponse
    {
        // Authorization handled by authorizeResource
        $this->projectService->deleteProject($project);
        return response()->json(['message' => 'Project deleted successfully']);
    }
}