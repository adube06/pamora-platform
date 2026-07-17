<?php

use App\Domains\People\Presentation\Http\Controllers\CommitteeController;
use App\Domains\People\Presentation\Http\Controllers\InvitationController;
use App\Domains\People\Presentation\Http\Controllers\RsvpController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/occasions/{occasion}/committee', [CommitteeController::class, 'index'])->name('occasions.committee');
    Route::post('/occasions/{occasion}/committee/invitations', [CommitteeController::class, 'store'])->name('occasions.committee.invite');
    Route::delete('/occasion-members/{occasionMember}', [CommitteeController::class, 'destroy'])->name('occasion-members.destroy');
    Route::patch('/occasion-members/{occasionMember}/responsibilities', [CommitteeController::class, 'updateResponsibilities'])->name('occasion-members.update-responsibilities');
    Route::patch('/occasion-members/{occasionMember}/role', [CommitteeController::class, 'updateRole'])->name('occasion-members.update-role');

    Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');

    Route::post('/occasions/{occasion}/rsvp', [RsvpController::class, 'store'])->name('occasions.rsvp.store');
    Route::post('/occasion-members/{occasionMember}/reopen-rsvp', [RsvpController::class, 'reopen'])->name('occasion-members.reopen-rsvp');
});

// The invitation landing page (and declining it) must be reachable while
// logged out, so the invited person can respond before registering/logging in.
Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
Route::post('/invitations/{token}/decline', [InvitationController::class, 'decline'])->name('invitations.decline');
