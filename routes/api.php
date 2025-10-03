<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\ScrapingController;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (require authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth routes that don't need tenant scope
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    
    // Routes that need tenant scope
    Route::middleware(['tenant'])->group(function () {
        // Projects
        Route::apiResource('projects', ProjectController::class);
        
        // Tasks - FIXED ROUTE STRUCTURE
        Route::prefix('projects/{project}')->group(function () {
            Route::get('tasks', [TaskController::class, 'index']);
            Route::post('tasks', [TaskController::class, 'store']);
            Route::get('tasks-all', [TaskController::class, 'projectTasks']);
        });
        
        // Individual task routes (outside project prefix for proper model binding)
        Route::prefix('tasks')->group(function () {
            Route::get('{task}', [TaskController::class, 'show']);
            Route::put('{task}', [TaskController::class, 'update']);
            Route::delete('{task}', [TaskController::class, 'destroy']);
            Route::post('{task}/assign-users', [TaskController::class, 'assignUsers']);
        });
        
        // Scraping
        Route::prefix('scraping')->group(function () {
            Route::get('/', [ScrapingController::class, 'index']);
            Route::post('/scrape', [ScrapingController::class, 'scrape']);
            Route::get('/export-csv', [ScrapingController::class, 'exportCsv']);
        });
        
        // Dashboard
        Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
            $company = $request->user()->company;
            
            return response()->json([
                'stats' => [
                    ['title' => 'Total Projects', 'value' => $company->projects()->count()],
                    ['title' => 'Active Tasks', 'value' => $company->tasks()->where('status', '!=', 'completed')->count()],
                    ['title' => 'Completed Tasks', 'value' => $company->tasks()->where('status', 'completed')->count()],
                ],
                'recent_projects' => $company->projects()->withCount('tasks')->latest()->take(5)->get(),
                'task_status_data' => [
                    'pending' => $company->tasks()->where('status', 'pending')->count(),
                    'in_progress' => $company->tasks()->where('status', 'in_progress')->count(),
                    'completed' => $company->tasks()->where('status', 'completed')->count(),
                ]
            ]);
        });
    });
});