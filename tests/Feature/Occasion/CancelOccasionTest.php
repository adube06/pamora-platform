<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets the host cancel an occasion before completion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Planning]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/cancel")
        ->assertSessionHasNoErrors();

    expect($occasion->fresh()->status)->toBe(OccasionStatus::Cancelled);
});

it('rejects cancelling a completed occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Completed]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/cancel")
        ->assertSessionHasErrors('status');

    expect($occasion->fresh()->status)->toBe(OccasionStatus::Completed);
});

it('rejects cancelling an already cancelled occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Cancelled]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/cancel")
        ->assertSessionHasErrors('status');
});

it('prevents a member without occasion.cancel from cancelling', function () {
    $occasion = Occasion::factory()->create(['status' => OccasionStatus::Draft]);
    $observerUser = User::factory()->create();
    OccasionMember::factory()->role(Role::Observer)->create([
        'occasion_id' => $occasion->id,
        'user_id' => $observerUser->id,
    ]);

    $this->actingAs($observerUser)
        ->post("/occasions/{$occasion->slug}/cancel")
        ->assertForbidden();

    expect($occasion->fresh()->status)->toBe(OccasionStatus::Draft);
});
