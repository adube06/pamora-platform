<?php

use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

it('sends a password reset notification for a known email', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'amina@example.com']);

    $this->post('/forgot-password', ['email' => 'amina@example.com'])
        ->assertSessionHasNoErrors();

    Notification::assertSentTo($user, ResetPassword::class);
});

it('resets the password with a valid token and logs the audit entry', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'amina@example.com', 'password' => Hash::make('old-password')]);

    $this->post('/forgot-password', ['email' => 'amina@example.com']);

    $token = null;
    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    $this->post('/reset-password', [
        'token' => $token,
        'email' => 'amina@example.com',
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ])->assertRedirect(route('login'));

    expect(Hash::check('brand-new-password', $user->fresh()->password))->toBeTrue()
        ->and(Hash::check('old-password', $user->fresh()->password))->toBeFalse();

    expect(ActivityLog::where('action', 'identity.password_reset')
        ->where('subject_id', $user->id)
        ->count())->toBe(1);
});

it('rejects an invalid reset token', function () {
    $user = User::factory()->create(['email' => 'amina@example.com']);

    $this->post('/reset-password', [
        'token' => 'not-a-real-token',
        'email' => 'amina@example.com',
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ])->assertSessionHasErrors('email');

    expect(Hash::check('brand-new-password', $user->fresh()->password))->toBeFalse();
});

it('rejects a mismatched password confirmation', function () {
    $this->post('/reset-password', [
        'token' => 'any-token',
        'email' => 'amina@example.com',
        'password' => 'brand-new-password',
        'password_confirmation' => 'different-password',
    ])->assertSessionHasErrors('password');
});
