<?php

use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

it('registers a new user and returns a bearer token', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Amina Hassan',
        'email' => 'amina@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.user.email', 'amina@example.com');
    expect($response->json('data.token'))->toBeString()->not->toBeEmpty();

    expect(User::where('email', 'amina@example.com')->exists())->toBeTrue();
});

it('rejects registration with an email that is already taken', function () {
    User::factory()->create(['email' => 'amina@example.com']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Amina Hassan',
        'email' => 'amina@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('success', false);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
    expect($response->json('error.fields.email'))->not->toBeEmpty();

    expect(User::where('email', 'amina@example.com')->count())->toBe(1);
});

it('logs in with valid credentials and returns a bearer token', function () {
    $user = User::factory()->create(['password' => 'password123']);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.user.id', $user->id);
    expect($response->json('data.token'))->toBeString()->not->toBeEmpty();
});

it('rejects login with an invalid password', function () {
    $user = User::factory()->create(['password' => 'password123']);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('success', false);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
    $response->assertJsonPath('error.fields.email.0', 'These credentials do not match our records.');
});

it('rate limits repeated login attempts from the same IP', function () {
    $payload = ['email' => 'nobody@example.com', 'password' => 'wrong-password'];

    for ($i = 0; $i < 6; $i++) {
        $this->postJson('/api/v1/auth/login', $payload);
    }

    $response = $this->postJson('/api/v1/auth/login', $payload);

    $response->assertStatus(429);
    $response->assertJsonPath('success', false);
    $response->assertJsonPath('error.code', 'TOO_MANY_REQUESTS');
});

it('revokes the current token on logout and logs the audit entry', function () {
    $user = User::factory()->create();
    $token = $user->createToken('mobile')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/logout');

    $response->assertOk();
    expect($user->tokens()->count())->toBe(0);

    expect(ActivityLog::where('action', 'identity.user_signed_out')
        ->where('subject_id', $user->id)
        ->count())->toBe(1);
});

it('sends a password reset notification via the API', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'amina@example.com']);

    $response = $this->postJson('/api/v1/auth/forgot-password', ['email' => 'amina@example.com']);

    $response->assertOk();
    $response->assertJsonPath('success', true);

    Notification::assertSentTo($user, ResetPassword::class);
});

it('resets the password via the API with a valid token', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'amina@example.com', 'password' => Hash::make('old-password')]);

    $this->postJson('/api/v1/auth/forgot-password', ['email' => 'amina@example.com']);

    $token = null;
    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    $response = $this->postJson('/api/v1/auth/reset-password', [
        'token' => $token,
        'email' => 'amina@example.com',
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);

    expect(Hash::check('brand-new-password', $user->fresh()->password))->toBeTrue();
});
