<?php

use App\Domains\Finance\Presentation\Http\Controllers\Api\BudgetController;
use App\Domains\Finance\Presentation\Http\Controllers\Api\BudgetItemController;
use App\Domains\Finance\Presentation\Http\Controllers\Api\ContributionController;
use App\Domains\Finance\Presentation\Http\Controllers\Api\ExpenseController;
use App\Domains\Finance\Presentation\Http\Controllers\Api\PledgeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/occasions/{occasion}/contributions', [ContributionController::class, 'index']);
    Route::post('/occasions/{occasion}/contributions', [ContributionController::class, 'store']);
    Route::get('/occasions/{occasion}/budget', [BudgetController::class, 'show']);
    Route::post('/occasions/{occasion}/budget', [BudgetController::class, 'store']);
    Route::get('/occasions/{occasion}/expenses', [ExpenseController::class, 'index']);
    Route::post('/occasions/{occasion}/expenses', [ExpenseController::class, 'store']);
    Route::post('/occasions/{occasion}/budget-items', [BudgetItemController::class, 'store']);
    Route::get('/occasions/{occasion}/pledges', [PledgeController::class, 'index']);
    Route::post('/occasions/{occasion}/pledges', [PledgeController::class, 'store']);
    Route::patch('/occasions/{occasion}/pledges/{pledge}', [PledgeController::class, 'update']);
});
