<?php

use App\Domains\Identity\Presentation\Http\Controllers\EmailVerificationController;
use App\Domains\Identity\Presentation\Http\Controllers\LoginController;
use App\Domains\Identity\Presentation\Http\Controllers\PasswordResetController;
use App\Domains\Identity\Presentation\Http\Controllers\ProfileController;
use App\Domains\Identity\Presentation\Http\Controllers\RegisterController;
use App\Domains\Identity\Presentation\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    Route::delete('/sessions/{sessionId}', [SessionController::class, 'destroy'])->name('sessions.destroy');
    Route::post('/sessions/logout-others', [SessionController::class, 'destroyOthers'])->name('sessions.destroy-others');
});
