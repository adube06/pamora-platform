<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets the host archive a completed occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Completed]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/archive")
        ->assertSessionHasNoErrors();

    expect($occasion->fresh()->status)->toBe(OccasionStatus::Archived);
});

it('rejects archiving an occasion that is not completed', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Active]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/archive")
        ->assertSessionHasErrors('status');

    expect($occasion->fresh()->status)->toBe(OccasionStatus::Active);
});

it('prevents a member without occasion.archive from archiving', function () {
    $occasion = Occasion::factory()->create(['status' => OccasionStatus::Completed]);
    $observerUser = User::factory()->create();
    OccasionMember::factory()->role(Role::Observer)->create([
        'occasion_id' => $occasion->id,
        'user_id' => $observerUser->id,
    ]);

    $this->actingAs($observerUser)
        ->post("/occasions/{$occasion->slug}/archive")
        ->assertForbidden();

    expect($occasion->fresh()->status)->toBe(OccasionStatus::Completed);
});
