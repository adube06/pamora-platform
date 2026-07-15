<?php

use App\Domains\Finance\Presentation\Http\Controllers\Api\BudgetController;
use App\Domains\Finance\Presentation\Http\Controllers\Api\ContributionController;
use App\Domains\Finance\Presentation\Http\Controllers\Api\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/occasions/{occasion}/contributions', [ContributionController::class, 'index']);
    Route::post('/occasions/{occasion}/contributions', [ContributionController::class, 'store']);
    Route::get('/occasions/{occasion}/budget', [BudgetController::class, 'show']);
    Route::post('/occasions/{occasion}/budget', [BudgetController::class, 'store']);
    Route::get('/occasions/{occasion}/expenses', [ExpenseController::class, 'index']);
    Route::post('/occasions/{occasion}/expenses', [ExpenseController::class, 'store']);
});
