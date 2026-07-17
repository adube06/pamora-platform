<?php

use App\Domains\Marketplace\Presentation\Http\Controllers\BookingController;
use App\Domains\Marketplace\Presentation\Http\Controllers\OccasionMarketplaceController;
use App\Domains\Marketplace\Presentation\Http\Controllers\QuotationController;
use App\Domains\Marketplace\Presentation\Http\Controllers\RentalItemController;
use App\Domains\Marketplace\Presentation\Http\Controllers\ReviewController;
use App\Domains\Marketplace\Presentation\Http\Controllers\ServiceController;
use App\Domains\Marketplace\Presentation\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/vendor', [VendorController::class, 'index'])->name('vendor.index');
    Route::post('/vendor', [VendorController::class, 'store'])->name('vendor.store');
    Route::post('/vendor/{vendor}/services', [ServiceController::class, 'store'])->name('vendor.services.store');
    Route::patch('/vendor/services/{service}', [ServiceController::class, 'update'])->name('vendor.services.update');
    Route::post('/vendor/{vendor}/rental-items', [RentalItemController::class, 'store'])->name('vendor.rental-items.store');
    Route::patch('/vendor/rental-items/{rentalItem}', [RentalItemController::class, 'update'])->name('vendor.rental-items.update');

    Route::get('/occasions/{occasion}/marketplace', [OccasionMarketplaceController::class, 'index'])->name('occasions.marketplace');
    Route::post('/occasions/{occasion}/quotations', [QuotationController::class, 'store'])->name('occasions.quotations.store');
    Route::patch('/quotations/{quotation}/submit', [QuotationController::class, 'submit'])->name('quotations.submit');
    Route::patch('/quotations/{quotation}/accept', [QuotationController::class, 'accept'])->name('quotations.accept');
    Route::patch('/quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');
    Route::patch('/quotations/{quotation}/confirm', [QuotationController::class, 'confirm'])->name('quotations.confirm');
    Route::patch('/bookings/{booking}/complete', [BookingController::class, 'complete'])->name('bookings.complete');
    Route::post('/bookings/{booking}/review', [ReviewController::class, 'store'])->name('bookings.review.store');
});
