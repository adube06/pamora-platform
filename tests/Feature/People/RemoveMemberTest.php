<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets an authorized member remove another member', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $target = OccasionMember::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->delete("/occasion-members/{$target->uuid}")
        ->assertSessionHasNoErrors();

    expect(OccasionMember::find($target->id))->toBeNull()
        ->and(OccasionMember::withTrashed()->find($target->id))->not->toBeNull();
});

it('rejects removing a member once the occasion is completed', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Completed]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $target = OccasionMember::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->delete("/occasion-members/{$target->uuid}")
        ->assertSessionHasErrors('member');

    expect(OccasionMember::find($target->id))->not->toBeNull();
});

it('rejects removing the host', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    $hostMember = OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)
        ->delete("/occasion-members/{$hostMember->uuid}")
        ->assertSessionHasErrors('member');

    expect(OccasionMember::find($hostMember->id))->not->toBeNull();
});

it('prevents a member without people.remove_member from removing a member', function () {
    $occasion = Occasion::factory()->create();
    $target = OccasionMember::factory()->create(['occasion_id' => $occasion->id]);
    $observerUser = User::factory()->create();
    OccasionMember::factory()->role(Role::Observer)->create([
        'occasion_id' => $occasion->id,
        'user_id' => $observerUser->id,
    ]);

    $this->actingAs($observerUser)
        ->delete("/occasion-members/{$target->uuid}")
        ->assertForbidden();

    expect(OccasionMember::find($target->id))->not->toBeNull();
});
