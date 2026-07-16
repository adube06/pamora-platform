<?php

use App\Domains\Occasion\Presentation\Http\Controllers\Api\OccasionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/occasions', [OccasionController::class, 'index']);
    Route::post('/occasions', [OccasionController::class, 'store']);
    Route::get('/occasions/{occasion}', [OccasionController::class, 'show']);
    Route::patch('/occasions/{occasion}', [OccasionController::class, 'update']);
    Route::post('/occasions/{occasion}/archive', [OccasionController::class, 'archive']);
    Route::post('/occasions/{occasion}/cancel', [OccasionController::class, 'cancel']);
});
