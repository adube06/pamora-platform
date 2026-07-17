<?php

use App\Domains\Marketplace\Presentation\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/vendor', [VendorController::class, 'index'])->name('vendor.index');
    Route::post('/vendor', [VendorController::class, 'store'])->name('vendor.store');
});
