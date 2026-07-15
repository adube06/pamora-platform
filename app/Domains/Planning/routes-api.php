<?php

use App\Domains\Planning\Presentation\Http\Controllers\Api\ChecklistController;
use App\Domains\Planning\Presentation\Http\Controllers\Api\MilestoneController;
use App\Domains\Planning\Presentation\Http\Controllers\Api\TaskController;
use App\Domains\Planning\Presentation\Http\Controllers\Api\TimelineEventController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/occasions/{occasion}/tasks', [TaskController::class, 'index']);
    Route::post('/occasions/{occasion}/tasks', [TaskController::class, 'store']);
    Route::post('/tasks/{task}/assign', [TaskController::class, 'assign']);
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete']);
    Route::post('/tasks/{task}/reopen', [TaskController::class, 'reopen']);
    Route::get('/occasions/{occasion}/checklists', [ChecklistController::class, 'index']);
    Route::post('/occasions/{occasion}/checklists', [ChecklistController::class, 'store']);
    Route::get('/occasions/{occasion}/milestones', [MilestoneController::class, 'index']);
    Route::post('/occasions/{occasion}/milestones', [MilestoneController::class, 'store']);
    Route::get('/occasions/{occasion}/timeline-events', [TimelineEventController::class, 'index']);
    Route::post('/occasions/{occasion}/timeline-events', [TimelineEventController::class, 'store']);
});
