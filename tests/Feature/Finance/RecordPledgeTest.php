<?php

use App\Domains\Finance\Domain\Enums\PledgeStatus;
use App\Domains\Finance\Domain\Models\Pledge;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets an authorized member record a pledge, defaulting to pending', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $response = $this->actingAs($host)->post("/occasions/{$occasion->slug}/pledges", [
        'pledgor_name' => 'Amina Hassan',
        'amount' => 50000,
        'pledged_at' => now()->toDateString(),
    ]);

    $response->assertSessionHasNoErrors();

    $pledge = Pledge::firstWhere('pledgor_name', 'Amina Hassan');
    expect($pledge)->not->toBeNull()
        ->and($pledge->status)->toBe(PledgeStatus::Pending)
        ->and($pledge->currency)->toBe('TZS');
});

it('lets an authorized member record a pledge as already confirmed', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/pledges", [
        'pledgor_name' => 'Amina Hassan',
        'amount' => 50000,
        'status' => 'confirmed',
        'pledged_at' => now()->toDateString(),
    ]);

    $pledge = Pledge::firstWhere('pledgor_name', 'Amina Hassan');
    expect($pledge->status)->toBe(PledgeStatus::Confirmed);
});

it('prevents a member without finance.record_pledge from recording a pledge', function () {
    $occasion = Occasion::factory()->create();
    $memberUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $memberUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($memberUser)
        ->post("/occasions/{$occasion->slug}/pledges", [
            'pledgor_name' => 'Should not save',
            'amount' => 5000,
            'pledged_at' => now()->toDateString(),
        ])
        ->assertForbidden();

    expect(Pledge::where('pledgor_name', 'Should not save')->exists())->toBeFalse();
});

it('lets an authorized member update a pledge\'s status', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $pledge = Pledge::factory()->create(['occasion_id' => $occasion->id, 'status' => PledgeStatus::Pending]);

    $this->actingAs($host)
        ->patch("/occasions/{$occasion->slug}/pledges/{$pledge->uuid}", ['status' => 'confirmed'])
        ->assertSessionHasNoErrors();

    expect($pledge->fresh()->status)->toBe(PledgeStatus::Confirmed);
});

it('rejects updating a pledge that belongs to a different occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $otherPledge = Pledge::factory()->create(['status' => PledgeStatus::Pending]);

    $this->actingAs($host)
        ->patch("/occasions/{$occasion->slug}/pledges/{$otherPledge->uuid}", ['status' => 'confirmed'])
        ->assertForbidden();

    expect($otherPledge->fresh()->status)->toBe(PledgeStatus::Pending);
});
