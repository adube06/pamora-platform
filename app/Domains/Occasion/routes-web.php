<?php

use App\Domains\Occasion\Presentation\Http\Controllers\OccasionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/occasions', [OccasionController::class, 'index'])->name('occasions.index');
    Route::get('/occasions/create', [OccasionController::class, 'create'])->name('occasions.create');
    Route::post('/occasions', [OccasionController::class, 'store'])->name('occasions.store');
    Route::get('/occasions/{occasion}', [OccasionController::class, 'show'])->name('occasions.show');
});
