<?php

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets the host transfer ownership to an eligible member', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $newHostUser = User::factory()->create();
    $newHostMember = OccasionMember::factory()->role(Role::Treasurer)->create([
        'occasion_id' => $occasion->id,
        'user_id' => $newHostUser->id,
    ]);

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/transfer-ownership", ['member_uuid' => $newHostMember->uuid])
        ->assertSessionHasNoErrors();

    expect($occasion->fresh()->host_id)->toBe($newHostUser->id)
        ->and($newHostMember->fresh()->role)->toBe(Role::Host)
        ->and($newHostMember->fresh()->permissions)->toBe(Role::Host->permissions());

    $oldHostMember = OccasionMember::where('occasion_id', $occasion->id)->where('user_id', $host->id)->first();
    expect($oldHostMember->role)->toBe(Role::Chairperson)
        ->and($oldHostMember->permissions)->toBe(Role::Chairperson->permissions());
});

it('rejects a non-host actor transferring ownership', function () {
    $occasion = Occasion::factory()->create();
    $member = OccasionMember::factory()->role(Role::Member)->create(['occasion_id' => $occasion->id]);
    $target = OccasionMember::factory()->role(Role::Treasurer)->create(['occasion_id' => $occasion->id]);

    $this->actingAs($member->user)
        ->post("/occasions/{$occasion->slug}/transfer-ownership", ['member_uuid' => $target->uuid])
        ->assertForbidden();

    expect($occasion->fresh()->host_id)->not->toBe($member->user_id);
});

it('rejects transferring ownership to a guest', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $guestMember = OccasionMember::factory()->role(Role::Guest)->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/transfer-ownership", ['member_uuid' => $guestMember->uuid])
        ->assertSessionHasErrors('member');

    expect($occasion->fresh()->host_id)->toBe($host->id);
});

it('rejects transferring ownership to a member of a different occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $otherOccasionMember = OccasionMember::factory()->role(Role::Treasurer)->create();

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/transfer-ownership", ['member_uuid' => $otherOccasionMember->uuid])
        ->assertSessionHasErrors('member_uuid');
});
