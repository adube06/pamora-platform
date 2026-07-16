<?php

use App\Domains\People\Domain\Enums\InvitationStatus;
use App\Domains\People\Domain\Models\Invitation;

it('lets an invitee decline a pending invitation without authenticating', function () {
    $invitation = Invitation::factory()->create();

    $this->post("/invitations/{$invitation->token}/decline")
        ->assertSessionHasNoErrors();

    expect($invitation->fresh()->status)->toBe(InvitationStatus::Declined);
});

it('rejects declining an invitation that is already accepted', function () {
    $invitation = Invitation::factory()->create(['status' => InvitationStatus::Accepted]);

    $this->post("/invitations/{$invitation->token}/decline")
        ->assertSessionHasErrors('invitation');

    expect($invitation->fresh()->status)->toBe(InvitationStatus::Accepted);
});

it('rejects declining an expired invitation', function () {
    $invitation = Invitation::factory()->expired()->create();

    $this->post("/invitations/{$invitation->token}/decline")
        ->assertSessionHasErrors('invitation');
});
