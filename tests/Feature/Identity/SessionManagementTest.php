<?php

use App\Domains\Identity\Application\Services\RevokeOtherSessionsService;
use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function seedSession(string $id, int $userId, string $ip = '127.0.0.1', string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120 Safari/537'): void
{
    DB::table('sessions')->insert([
        'id' => $id,
        'user_id' => $userId,
        'ip_address' => $ip,
        'user_agent' => $userAgent,
        'payload' => base64_encode(serialize([])),
        'last_activity' => now()->timestamp,
    ]);
}

it('lets a user revoke their own session and logs the change', function () {
    $user = User::factory()->create();
    seedSession('session-own', $user->id);

    $this->actingAs($user)
        ->delete('/sessions/session-own')
        ->assertSessionHasNoErrors();

    expect(DB::table('sessions')->where('id', 'session-own')->exists())->toBeFalse();

    expect(ActivityLog::where('action', 'identity.session_revoked')
        ->where('subject_id', $user->id)
        ->count())->toBe(1);
});

it('does not let a user revoke another user\'s session', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    seedSession('session-foreign', $other->id);

    $this->actingAs($user)->delete('/sessions/session-foreign');

    expect(DB::table('sessions')->where('id', 'session-foreign')->exists())->toBeTrue();
});

it('signs out other devices but preserves the current session', function () {
    // Exercised directly against the Service rather than through HTTP: the
    // test suite forces SESSION_DRIVER=array (phpunit.xml), so there is no
    // real, stable session id to assert against across two separate test
    // requests — the Service is what the plan actually needs verified.
    $user = User::factory()->create();
    seedSession('current-device', $user->id);
    seedSession('other-device-1', $user->id, '2.2.2.2');
    seedSession('other-device-2', $user->id, '3.3.3.3');

    $count = app(RevokeOtherSessionsService::class)->handle($user, 'current-device', $user);

    expect($count)->toBe(2)
        ->and(DB::table('sessions')->where('user_id', $user->id)->pluck('id')->all())->toBe(['current-device']);

    expect(ActivityLog::where('action', 'identity.session_revoked')
        ->where('subject_id', $user->id)
        ->count())->toBe(2);
});

it('only lists the authenticated user\'s own sessions on the profile page', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    seedSession('session-mine', $user->id);
    seedSession('session-not-mine', $other->id);

    $this->actingAs($user)
        ->get('/profile')
        ->assertInertia(fn ($page) => $page
            ->component('Profile')
            ->has('sessions', 1)
            ->where('sessions.0.id', 'session-mine')
        );
});
