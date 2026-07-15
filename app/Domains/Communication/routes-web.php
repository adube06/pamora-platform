<?php

use App\Domains\Communication\Presentation\Http\Controllers\CommunicationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/occasions/{occasion}/communication', [CommunicationController::class, 'index'])->name('occasions.communication');
    Route::post('/occasions/{occasion}/announcements', [CommunicationController::class, 'store'])->name('occasions.announcements.store');
});
