<?php

use App\Domains\Communication\Presentation\Http\Controllers\Api\CommunicationController;
use App\Domains\Communication\Presentation\Http\Controllers\Api\NotificationController;
use App\Domains\Communication\Presentation\Http\Controllers\Api\ReminderRuleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/occasions/{occasion}/announcements', [CommunicationController::class, 'index']);
    Route::post('/occasions/{occasion}/announcements', [CommunicationController::class, 'store']);
    Route::post('/occasions/{occasion}/reminder-rules', [ReminderRuleController::class, 'store']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
});
