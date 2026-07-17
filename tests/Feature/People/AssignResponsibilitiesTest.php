<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets an authorized member assign responsibilities to a member', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $member = OccasionMember::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->patch("/occasion-members/{$member->uuid}/responsibilities", [
            'responsibilities' => ['catering_lead', 'transport_coordinator'],
        ])
        ->assertSessionHasNoErrors();

    expect($member->fresh()->responsibilities)->toBe(['catering_lead', 'transport_coordinator']);
});

it('fully replaces a member\'s previous responsibilities rather than merging', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $member = OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'responsibilities' => ['catering_lead']]);

    $this->actingAs($host)
        ->patch("/occasion-members/{$member->uuid}/responsibilities", ['responsibilities' => ['secretary']])
        ->assertSessionHasNoErrors();

    expect($member->fresh()->responsibilities)->toBe(['secretary']);
});

it('rejects an invalid responsibility value', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $member = OccasionMember::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->patch("/occasion-members/{$member->uuid}/responsibilities", ['responsibilities' => ['not-a-real-responsibility']])
        ->assertSessionHasErrors('responsibilities.0');

    expect($member->fresh()->responsibilities)->toBeNull();
});

it('rejects assigning responsibilities on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $member = OccasionMember::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->patch("/occasion-members/{$member->uuid}/responsibilities", ['responsibilities' => ['secretary']])
        ->assertSessionHasErrors('occasion');

    expect($member->fresh()->responsibilities)->toBeNull();
});

it('prevents a member without people.assign_responsibility from assigning responsibilities', function () {
    $occasion = Occasion::factory()->create();
    $member = OccasionMember::factory()->create(['occasion_id' => $occasion->id]);

    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($guestUser)
        ->patch("/occasion-members/{$member->uuid}/responsibilities", ['responsibilities' => ['secretary']])
        ->assertForbidden();

    expect($member->fresh()->responsibilities)->toBeNull();
});
