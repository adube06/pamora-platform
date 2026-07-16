<?php

use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

it('sends a verification notification when a user registers', function () {
    Notification::fake();

    $this->post('/register', [
        'name' => 'Amina Hassan',
        'email' => 'amina@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'amina@example.com')->first();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('marks the email as verified when visiting a valid signed link', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->actingAs($user)->get($url)->assertRedirect(route('occasions.index'));

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();

    expect(ActivityLog::where('action', 'identity.email_verified')
        ->where('subject_id', $user->id)
        ->count())->toBe(1);
});

it('rejects a tampered verification link', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email@example.com')],
    );

    $this->actingAs($user)->get($url)->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('lets an authenticated user resend the verification email', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->post('/email/verification-notification')
        ->assertSessionHasNoErrors();

    Notification::assertSentTo($user, VerifyEmail::class);
});
