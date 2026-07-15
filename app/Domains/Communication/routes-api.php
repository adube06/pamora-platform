<?php

use App\Domains\Communication\Presentation\Http\Controllers\Api\CommunicationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/occasions/{occasion}/announcements', [CommunicationController::class, 'index']);
    Route::post('/occasions/{occasion}/announcements', [CommunicationController::class, 'store']);
});
