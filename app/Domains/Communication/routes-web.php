<?php

use App\Domains\Communication\Presentation\Http\Controllers\CommunicationController;
use App\Domains\Communication\Presentation\Http\Controllers\NotificationController;
use App\Domains\Communication\Presentation\Http\Controllers\ReminderRuleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/occasions/{occasion}/communication', [CommunicationController::class, 'index'])->name('occasions.communication');
    Route::post('/occasions/{occasion}/announcements', [CommunicationController::class, 'store'])->name('occasions.announcements.store');
    Route::post('/occasions/{occasion}/reminder-rules', [ReminderRuleController::class, 'store'])->name('occasions.reminder-rules.store');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});
