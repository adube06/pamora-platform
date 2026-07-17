<?php

use App\Domains\Marketplace\Presentation\Http\Controllers\Api\QuotationController;
use App\Domains\Marketplace\Presentation\Http\Controllers\Api\ServiceController;
use App\Domains\Marketplace\Presentation\Http\Controllers\Api\VendorController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/vendor', [VendorController::class, 'index']);
    Route::post('/vendor', [VendorController::class, 'store']);
    Route::post('/vendor/{vendor}/services', [ServiceController::class, 'store']);
    Route::patch('/vendor/services/{service}', [ServiceController::class, 'update']);
    Route::post('/occasions/{occasion}/quotations', [QuotationController::class, 'store']);
    Route::patch('/quotations/{quotation}/submit', [QuotationController::class, 'submit']);
    Route::patch('/quotations/{quotation}/accept', [QuotationController::class, 'accept']);
    Route::patch('/quotations/{quotation}/reject', [QuotationController::class, 'reject']);
});
