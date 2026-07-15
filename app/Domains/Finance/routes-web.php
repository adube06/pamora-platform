<?php

use App\Domains\Finance\Presentation\Http\Controllers\FinanceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/occasions/{occasion}/finance', [FinanceController::class, 'index'])->name('occasions.finance');
    Route::post('/occasions/{occasion}/contributions', [FinanceController::class, 'store'])->name('occasions.contributions.store');
});
