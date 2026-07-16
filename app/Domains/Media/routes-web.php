<?php

use App\Domains\Media\Presentation\Http\Controllers\MediaController;
use App\Domains\Media\Presentation\Http\Controllers\MediaDownloadController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/occasions/{occasion}/media', [MediaController::class, 'index'])->name('occasions.media');
    Route::post('/occasions/{occasion}/media', [MediaController::class, 'store'])->name('occasions.media.store');
    Route::get('/media/{mediaAsset}/download', MediaDownloadController::class)->name('media.download');
});
