<?php

use App\Domains\Finance\Presentation\Http\Controllers\BudgetController;
use App\Domains\Finance\Presentation\Http\Controllers\BudgetItemController;
use App\Domains\Finance\Presentation\Http\Controllers\ExpenseController;
use App\Domains\Finance\Presentation\Http\Controllers\FinanceController;
use App\Domains\Finance\Presentation\Http\Controllers\PledgeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/occasions/{occasion}/finance', [FinanceController::class, 'index'])->name('occasions.finance');
    Route::post('/occasions/{occasion}/contributions', [FinanceController::class, 'store'])->name('occasions.contributions.store');
    Route::post('/occasions/{occasion}/budget', [BudgetController::class, 'store'])->name('occasions.budget.store');
    Route::post('/occasions/{occasion}/expenses', [ExpenseController::class, 'store'])->name('occasions.expenses.store');
    Route::post('/occasions/{occasion}/budget-items', [BudgetItemController::class, 'store'])->name('occasions.budget-items.store');
    Route::post('/occasions/{occasion}/pledges', [PledgeController::class, 'store'])->name('occasions.pledges.store');
    Route::patch('/occasions/{occasion}/pledges/{pledge}', [PledgeController::class, 'update'])->name('occasions.pledges.update');
});
