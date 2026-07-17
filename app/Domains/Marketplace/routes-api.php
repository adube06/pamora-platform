<?php

use App\Domains\Marketplace\Presentation\Http\Controllers\Api\VendorController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/vendor', [VendorController::class, 'index']);
    Route::post('/vendor', [VendorController::class, 'store']);
});
