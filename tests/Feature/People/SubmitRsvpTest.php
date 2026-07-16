<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets a member submit an rsvp', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $guestUser = User::factory()->create();
    $guestMember = OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $guestUser->id]);

    $response = $this->actingAs($guestUser)->post("/occasions/{$occasion->slug}/rsvp", [
        'rsvp_status' => 'attending',
        'guest_count' => 2,
        'rsvp_message' => 'Looking forward to it!',
    ]);

    $response->assertSessionHasNoErrors();

    $guestMember->refresh();
    expect($guestMember->rsvp_status->value)->toBe('attending')
        ->and($guestMember->guest_count)->toBe(2)
        ->and($guestMember->rsvp_message)->toBe('Looking forward to it!')
        ->and($guestMember->rsvp_responded_at)->not->toBeNull();
});

it('rejects a second rsvp submission (BR-013)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'rsvp_status' => 'attending',
        'rsvp_responded_at' => now(),
    ]);

    $this->actingAs($guestUser)
        ->post("/occasions/{$occasion->slug}/rsvp", ['rsvp_status' => 'maybe'])
        ->assertSessionHasErrors('rsvp_status');
});

it('prevents a non-member from submitting an rsvp', function () {
    $occasion = Occasion::factory()->create();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->post("/occasions/{$occasion->slug}/rsvp", ['rsvp_status' => 'attending'])
        ->assertForbidden();
});

it('rejects submitting an rsvp on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $guestUser = User::factory()->create();
    $guestMember = OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $guestUser->id]);

    $this->actingAs($guestUser)
        ->post("/occasions/{$occasion->slug}/rsvp", ['rsvp_status' => 'attending'])
        ->assertSessionHasErrors('occasion');

    expect($guestMember->fresh()->rsvp_status)->toBeNull();
});
