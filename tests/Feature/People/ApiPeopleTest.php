<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\InvitationStatus;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\Invitation;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function occasionWithHostApi(): array
{
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'created_by' => $host->id]);
    $hostMember = OccasionMember::factory()->host()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $host->id,
    ]);

    return [$occasion, $host, $hostMember];
}

// --- Invitations ---

it('lets an authorized member invite someone via the API', function () {
    [$occasion, $host] = occasionWithHostApi();
    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/invitations", [
        'email' => 'treasurer@example.com',
        'role' => 'treasurer',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.email', 'treasurer@example.com');
    expect($response->json('data.token'))->toBeString()->not->toBeEmpty();

    expect(Invitation::where('email', 'treasurer@example.com')->exists())->toBeTrue();
});

it('rejects an invite from a member without people.invite_member', function () {
    [$occasion] = occasionWithHostApi();
    $guest = User::factory()->create();
    OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $guest->id, 'permissions' => []]);

    Sanctum::actingAs($guest);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/invitations", ['email' => 'x@example.com', 'role' => 'member']);

    $response->assertStatus(403);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
});

it('rejects inviting a member to an archived occasion via the API', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'created_by' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/invitations", ['email' => 'x@example.com', 'role' => 'member']);

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('logs an audit entry when a member is invited via the API', function () {
    [$occasion, $host] = occasionWithHostApi();
    Sanctum::actingAs($host);

    $this->postJson("/api/v1/occasions/{$occasion->slug}/invitations", ['email' => 'audit@example.com', 'role' => 'member']);

    $invitation = Invitation::firstWhere('email', 'audit@example.com');

    expect(ActivityLog::where('action', 'people.member_invited')
        ->where('subject_id', $invitation->id)
        ->count())->toBe(1);
});

it('accepts an invitation via the API and creates a membership', function () {
    $invitation = Invitation::factory()->create(['email' => 'treasurer@example.com', 'role' => Role::Treasurer]);
    $user = User::factory()->create(['email' => 'treasurer@example.com']);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/invitations/{$invitation->token}/accept");

    $response->assertOk();
    $response->assertJsonPath('data.status', 'active');

    $member = OccasionMember::firstWhere(['occasion_id' => $invitation->occasion_id, 'user_id' => $user->id]);
    expect($member)->not->toBeNull()->and($member->role)->toBe(Role::Treasurer);
});

it('rejects accepting an invitation with the wrong account via the API', function () {
    $invitation = Invitation::factory()->create(['email' => 'intended@example.com']);
    $wrongUser = User::factory()->create(['email' => 'someone-else@example.com']);

    Sanctum::actingAs($wrongUser);

    $response = $this->postJson("/api/v1/invitations/{$invitation->token}/accept");

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('returns a not-found envelope for an unknown invitation token', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/v1/invitations/not-a-real-token/accept');

    $response->assertStatus(404);
    $response->assertJsonPath('error.code', 'NOT_FOUND');
});

it('declines an invitation via the API without requiring authentication', function () {
    $invitation = Invitation::factory()->create();

    $response = $this->postJson("/api/v1/invitations/{$invitation->token}/decline");

    $response->assertOk();
    $response->assertJsonPath('data.status', 'declined');
    expect($invitation->fresh()->status)->toBe(InvitationStatus::Declined);
});

// --- Members list ---

it('lists members, pending invitations, and permission flags for an occasion', function () {
    [$occasion, $host] = occasionWithHostApi();
    $invitation = Invitation::factory()->create(['occasion_id' => $occasion->id, 'invited_by' => $host->id, 'email' => 'pending@example.com']);

    Sanctum::actingAs($host);

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/members");

    $response->assertOk();
    $response->assertJsonCount(1, 'data.members');
    $response->assertJsonCount(1, 'data.pending_invitations');
    $response->assertJsonPath('data.pending_invitations.0.email', 'pending@example.com');
    $response->assertJsonPath('data.permissions.can_invite', true);
    expect($response->json('data.pending_invitations.0'))->not->toHaveKey('token');
});

it('rejects listing members for a non-member', function () {
    [$occasion] = occasionWithHostApi();
    Sanctum::actingAs(User::factory()->create());

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/members");

    $response->assertStatus(403);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
});

// --- Remove member ---

it('lets an authorized member remove another member via the API', function () {
    [$occasion, $host] = occasionWithHostApi();
    $target = User::factory()->create();
    $member = OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $target->id]);

    Sanctum::actingAs($host);

    $response = $this->deleteJson("/api/v1/occasion-members/{$member->uuid}");

    $response->assertOk();
    $response->assertJsonPath('success', true);
    expect(OccasionMember::find($member->id))->toBeNull();
});

it('rejects removing the Host via the API', function () {
    [$occasion, $host, $hostMember] = occasionWithHostApi();
    Sanctum::actingAs($host);

    $response = $this->deleteJson("/api/v1/occasion-members/{$hostMember->uuid}");

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

// --- Responsibilities & role ---

it('lets an authorized member assign responsibilities via the API', function () {
    [$occasion, $host] = occasionWithHostApi();
    $target = User::factory()->create();
    $member = OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $target->id]);

    Sanctum::actingAs($host);

    $response = $this->patchJson("/api/v1/occasion-members/{$member->uuid}/responsibilities", [
        'responsibilities' => ['treasurer'],
    ]);

    $response->assertOk();
    expect($member->fresh()->responsibilities)->toBe(['treasurer']);
});

it('lets an authorized member change another member role via the API', function () {
    [$occasion, $host] = occasionWithHostApi();
    $target = User::factory()->create();
    $member = OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $target->id]);

    Sanctum::actingAs($host);

    $response = $this->patchJson("/api/v1/occasion-members/{$member->uuid}/role", ['role' => 'secretary']);

    $response->assertOk();
    expect($member->fresh()->role)->toBe(Role::Secretary);
});

it('rejects changing the Host role via the API', function () {
    [$occasion, $host, $hostMember] = occasionWithHostApi();
    Sanctum::actingAs($host);

    $response = $this->patchJson("/api/v1/occasion-members/{$hostMember->uuid}/role", ['role' => 'secretary']);

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

// --- RSVP ---

it('lets a member submit an RSVP via the API', function () {
    [$occasion, $host] = occasionWithHostApi();
    $guest = User::factory()->create();
    OccasionMember::factory()->role(Role::Guest)->create(['occasion_id' => $occasion->id, 'user_id' => $guest->id]);

    Sanctum::actingAs($guest);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/rsvp", [
        'rsvp_status' => 'attending',
        'guest_count' => 2,
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.rsvp_status', 'attending');
});

it('rejects a non-member submitting an RSVP via the API', function () {
    [$occasion] = occasionWithHostApi();
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/rsvp", ['rsvp_status' => 'attending']);

    $response->assertStatus(403);
});

it('rejects a duplicate RSVP via the API', function () {
    [$occasion] = occasionWithHostApi();
    $guest = User::factory()->create();
    OccasionMember::factory()->role(Role::Guest)->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guest->id,
        'rsvp_status' => 'attending',
        'rsvp_responded_at' => now(),
    ]);

    Sanctum::actingAs($guest);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/rsvp", ['rsvp_status' => 'not_attending']);

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('lets the host reopen RSVP for a member via the API', function () {
    [$occasion, $host] = occasionWithHostApi();
    $guest = User::factory()->create();
    $member = OccasionMember::factory()->role(Role::Guest)->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guest->id,
        'rsvp_status' => 'attending',
        'rsvp_responded_at' => now(),
    ]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasion-members/{$member->uuid}/reopen-rsvp");

    $response->assertOk();
    $response->assertJsonPath('data.rsvp_status', null);
});

it('rejects a non-host reopening RSVP via the API', function () {
    [$occasion] = occasionWithHostApi();
    $guest = User::factory()->create();
    $member = OccasionMember::factory()->role(Role::Guest)->create(['occasion_id' => $occasion->id, 'user_id' => $guest->id]);

    Sanctum::actingAs($guest);

    $response = $this->postJson("/api/v1/occasion-members/{$member->uuid}/reopen-rsvp");

    $response->assertStatus(403);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
});
