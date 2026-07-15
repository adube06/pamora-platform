<?php

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\InvitationStatus;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\Invitation;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

function occasionWithHost(): array
{
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'created_by' => $host->id]);
    $hostMember = OccasionMember::factory()->host()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $host->id,
    ]);

    return [$occasion, $host, $hostMember];
}

it('creates a pending invitation, not a membership, when a host invites someone', function () {
    [$occasion, $host] = occasionWithHost();

    $response = $this->actingAs($host)->post("/occasions/{$occasion->slug}/committee/invitations", [
        'email' => 'treasurer@example.com',
        'role' => 'treasurer',
        'notes' => 'Handles the wedding fund',
    ]);

    $response->assertSessionHasNoErrors();

    $invitation = Invitation::firstWhere('email', 'treasurer@example.com');

    expect($invitation)->not->toBeNull()
        ->and($invitation->status)->toBe(InvitationStatus::Pending)
        ->and($invitation->role)->toBe(Role::Treasurer)
        ->and(OccasionMember::where('occasion_id', $occasion->id)->where('user_id', '!=', $host->id)->exists())->toBeFalse();
});

it('rejects an invitation with an invalid role', function () {
    [$occasion, $host] = occasionWithHost();

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/committee/invitations", [
            'email' => 'someone@example.com',
            'role' => 'host',
        ])
        ->assertSessionHasErrors('role');
});

it('prevents a member without people.invite_member from inviting anyone', function () {
    [$occasion] = occasionWithHost();

    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($guestUser)
        ->post("/occasions/{$occasion->slug}/committee/invitations", ['email' => 'someone@example.com'])
        ->assertForbidden();
});
