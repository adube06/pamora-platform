<?php

use App\Domains\People\Presentation\Http\Controllers\Api\InvitationController;
use App\Domains\People\Presentation\Http\Controllers\Api\MemberController;
use App\Domains\People\Presentation\Http\Controllers\Api\RsvpController;
use Illuminate\Support\Facades\Route;

// Declining an invitation is unauthenticated and unconditional — the token
// itself is the credential (DeclineInvitationRequest::authorize()'s own
// reasoning), mirroring routes-web.php's invitations.decline being reachable
// while logged out. Requiring a Sanctum token here would force someone who
// isn't a Pamora user yet to register just to say no.
Route::post('/invitations/{token}/decline', [InvitationController::class, 'decline']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/occasions/{occasion}/members', [MemberController::class, 'index']);
    Route::post('/occasions/{occasion}/invitations', [InvitationController::class, 'store']);
    Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept']);
    Route::post('/occasions/{occasion}/rsvp', [RsvpController::class, 'store']);
    Route::post('/occasion-members/{occasionMember}/reopen-rsvp', [RsvpController::class, 'reopen']);
    Route::delete('/occasion-members/{occasionMember}', [MemberController::class, 'destroy']);
    Route::patch('/occasion-members/{occasionMember}/responsibilities', [MemberController::class, 'updateResponsibilities']);
    Route::patch('/occasion-members/{occasionMember}/role', [MemberController::class, 'updateRole']);
});
