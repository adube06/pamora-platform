<?php

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets the host reopen a responded members rsvp', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $guestUser = User::factory()->create();
    $guestMember = OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'rsvp_status' => 'not_attending',
        'rsvp_responded_at' => now(),
        'guest_count' => 0,
    ]);

    $response = $this->actingAs($host)->post("/occasion-members/{$guestMember->uuid}/reopen-rsvp");

    $response->assertSessionHasNoErrors();

    $guestMember->refresh();
    expect($guestMember->rsvp_status)->toBeNull()
        ->and($guestMember->rsvp_responded_at)->toBeNull()
        ->and($guestMember->guest_count)->toBeNull();

    // A fresh submission should now succeed.
    $this->actingAs($guestUser)
        ->post("/occasions/{$occasion->slug}/rsvp", ['rsvp_status' => 'attending'])
        ->assertSessionHasNoErrors();

    expect($guestMember->fresh()->rsvp_status->value)->toBe('attending');
});

it('prevents a non-host member from reopening someone elses rsvp', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $otherMember = OccasionMember::factory()->create(['occasion_id' => $occasion->id]);
    $targetMember = OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'rsvp_status' => 'attending',
        'rsvp_responded_at' => now(),
    ]);

    $this->actingAs($otherMember->user)
        ->post("/occasion-members/{$targetMember->uuid}/reopen-rsvp")
        ->assertForbidden();

    expect($targetMember->fresh()->rsvp_status->value)->toBe('attending');
});
