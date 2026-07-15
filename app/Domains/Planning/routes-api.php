<?php

use App\Domains\Planning\Presentation\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/occasions/{occasion}/tasks', [TaskController::class, 'index']);
    Route::post('/occasions/{occasion}/tasks', [TaskController::class, 'store']);
    Route::post('/tasks/{task}/assign', [TaskController::class, 'assign']);
});
