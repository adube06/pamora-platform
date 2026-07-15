<?php

use App\Domains\Finance\Presentation\Http\Controllers\Api\ContributionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/occasions/{occasion}/contributions', [ContributionController::class, 'index']);
    Route::post('/occasions/{occasion}/contributions', [ContributionController::class, 'store']);
});
