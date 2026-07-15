<?php

use App\Domains\Planning\Presentation\Http\Controllers\ChecklistController;
use App\Domains\Planning\Presentation\Http\Controllers\PlanningController;
use App\Domains\Planning\Presentation\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/occasions/{occasion}/planning', [PlanningController::class, 'index'])->name('occasions.planning');
    Route::post('/occasions/{occasion}/tasks', [PlanningController::class, 'store'])->name('occasions.tasks.store');
    Route::post('/tasks/{task}/assign', [TaskController::class, 'assign'])->name('tasks.assign');
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::post('/tasks/{task}/reopen', [TaskController::class, 'reopen'])->name('tasks.reopen');
    Route::post('/occasions/{occasion}/checklists', [ChecklistController::class, 'store'])->name('occasions.checklists.store');
});
