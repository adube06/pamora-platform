<?php

use App\Domains\Identity\Presentation\Http\Controllers\Api\AuthController;
use App\Domains\Identity\Presentation\Http\Controllers\Api\SessionController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/email/verification-notification', [AuthController::class, 'resendVerification']);
    Route::get('/sessions', [SessionController::class, 'index']);
    Route::delete('/sessions/{sessionId}', [SessionController::class, 'destroy']);
});
