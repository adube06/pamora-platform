<?php

use App\Domains\Media\Presentation\Http\Controllers\Api\AlbumController;
use App\Domains\Media\Presentation\Http\Controllers\Api\MediaAssetController;
use App\Domains\Media\Presentation\Http\Controllers\Api\MediaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/occasions/{occasion}/media', [MediaController::class, 'index']);
    Route::post('/occasions/{occasion}/media', [MediaController::class, 'store']);
    Route::post('/occasions/{occasion}/albums', [AlbumController::class, 'store']);
    Route::patch('/media/{mediaAsset}/move', [MediaAssetController::class, 'move']);
});
