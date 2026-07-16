<?php

use App\Domains\People\Presentation\Http\Controllers\CommitteeController;
use App\Domains\People\Presentation\Http\Controllers\InvitationController;
use App\Domains\People\Presentation\Http\Controllers\RsvpController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/occasions/{occasion}/committee', [CommitteeController::class, 'index'])->name('occasions.committee');
    Route::post('/occasions/{occasion}/committee/invitations', [CommitteeController::class, 'store'])->name('occasions.committee.invite');

    Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');

    Route::post('/occasions/{occasion}/rsvp', [RsvpController::class, 'store'])->name('occasions.rsvp.store');
    Route::post('/occasion-members/{occasionMember}/reopen-rsvp', [RsvpController::class, 'reopen'])->name('occasion-members.reopen-rsvp');
});

// The invitation landing page must be reachable while logged out, so the
// invited person can see what they're accepting before registering/logging in.
Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
