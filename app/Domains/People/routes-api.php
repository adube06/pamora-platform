<?php

use App\Domains\People\Presentation\Http\Controllers\Api\InvitationController;
use App\Domains\People\Presentation\Http\Controllers\Api\MemberController;
use App\Domains\People\Presentation\Http\Controllers\Api\RsvpController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/occasions/{occasion}/invitations', [InvitationController::class, 'store']);
    Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept']);
    Route::post('/invitations/{token}/decline', [InvitationController::class, 'decline']);
    Route::post('/occasions/{occasion}/rsvp', [RsvpController::class, 'store']);
    Route::post('/occasion-members/{occasionMember}/reopen-rsvp', [RsvpController::class, 'reopen']);
    Route::delete('/occasion-members/{occasionMember}', [MemberController::class, 'destroy']);
});
