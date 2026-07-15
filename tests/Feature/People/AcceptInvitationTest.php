<?php

use App\Domains\People\Domain\Enums\InvitationStatus;
use App\Domains\People\Domain\Models\Invitation;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

/**
 * This is the literal example ADR-014 uses to define what "testing the
 * business" means: verify that accepting an Invitation creates an
 * OccasionMember — not that a controller called a particular method.
 */
it('creates an OccasionMember when an invitation is accepted', function () {
    $invitation = Invitation::factory()->create([
        'email' => 'treasurer@example.com',
        'responsibilities' => ['treasurer'],
        'permissions' => ['finance.record_contribution'],
    ]);

    $user = User::factory()->create(['email' => 'treasurer@example.com']);

    $response = $this->actingAs($user)->post("/invitations/{$invitation->token}/accept");

    $response->assertSessionHasNoErrors();

    $member = OccasionMember::firstWhere(['occasion_id' => $invitation->occasion_id, 'user_id' => $user->id]);

    expect($member)->not->toBeNull()
        ->and($member->responsibilities)->toBe(['treasurer'])
        ->and($member->permissions)->toBe(['finance.record_contribution']);

    expect($invitation->fresh()->status)->toBe(InvitationStatus::Accepted);
});

it('rejects acceptance when the invitation has expired', function () {
    $invitation = Invitation::factory()->expired()->create(['email' => 'late@example.com']);
    $user = User::factory()->create(['email' => 'late@example.com']);

    $this->actingAs($user)
        ->post("/invitations/{$invitation->token}/accept")
        ->assertSessionHasErrors('invitation');

    expect(OccasionMember::where('user_id', $user->id)->exists())->toBeFalse();
});

it('rejects acceptance when the logged-in user email does not match the invitation', function () {
    $invitation = Invitation::factory()->create(['email' => 'intended@example.com']);
    $wrongUser = User::factory()->create(['email' => 'someone-else@example.com']);

    $this->actingAs($wrongUser)
        ->post("/invitations/{$invitation->token}/accept")
        ->assertSessionHasErrors('invitation');

    expect(OccasionMember::where('user_id', $wrongUser->id)->exists())->toBeFalse();
});

it('rejects acceptance of an already-revoked invitation', function () {
    $invitation = Invitation::factory()->create([
        'email' => 'revoked@example.com',
        'status' => InvitationStatus::Revoked,
    ]);
    $user = User::factory()->create(['email' => 'revoked@example.com']);

    $this->actingAs($user)
        ->post("/invitations/{$invitation->token}/accept")
        ->assertSessionHasErrors('invitation');
});
