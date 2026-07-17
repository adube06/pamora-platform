<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Shared\Domain\Enums\Permission;
use App\Models\User;

it('lets an authorized member change another member\'s role', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $member = OccasionMember::factory()->role(Role::Member)->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->patch("/occasion-members/{$member->uuid}/role", ['role' => 'treasurer'])
        ->assertSessionHasNoErrors();

    $member->refresh();
    expect($member->role)->toBe(Role::Treasurer)
        ->and($member->permissions)->toBe(Role::Treasurer->permissions())
        ->and($member->hasPermission(Permission::FinanceRecordPledge))->toBeTrue();
});

it('rejects changing a member\'s role to host', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $member = OccasionMember::factory()->role(Role::Member)->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->patch("/occasion-members/{$member->uuid}/role", ['role' => 'host'])
        ->assertSessionHasErrors('role');

    expect($member->fresh()->role)->toBe(Role::Member);
});

it('rejects changing the host\'s own role', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    $hostMember = OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)
        ->patch("/occasion-members/{$hostMember->uuid}/role", ['role' => 'chairperson'])
        ->assertSessionHasErrors('role');

    expect($hostMember->fresh()->role)->toBe(Role::Host);
});

it('rejects changing a member\'s role on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $member = OccasionMember::factory()->role(Role::Member)->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->patch("/occasion-members/{$member->uuid}/role", ['role' => 'treasurer'])
        ->assertSessionHasErrors('occasion');

    expect($member->fresh()->role)->toBe(Role::Member);
});

it('prevents a member without people.manage_permissions from changing a role', function () {
    $occasion = Occasion::factory()->create();
    $member = OccasionMember::factory()->role(Role::Member)->create(['occasion_id' => $occasion->id]);

    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($guestUser)
        ->patch("/occasion-members/{$member->uuid}/role", ['role' => 'treasurer'])
        ->assertForbidden();

    expect($member->fresh()->role)->toBe(Role::Member);
});
