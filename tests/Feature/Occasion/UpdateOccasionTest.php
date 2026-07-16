<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets the host edit occasion details', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'title' => 'Old Title']);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)
        ->patch("/occasions/{$occasion->slug}", [
            'title' => 'New Title',
            'type' => $occasion->type->value,
        ])
        ->assertSessionHasNoErrors();

    expect($occasion->fresh()->title)->toBe('New Title');
});

it('prevents a member without occasion.edit from editing', function () {
    $occasion = Occasion::factory()->create();
    $observerUser = User::factory()->create();
    OccasionMember::factory()->role(Role::Observer)->create([
        'occasion_id' => $occasion->id,
        'user_id' => $observerUser->id,
    ]);

    $this->actingAs($observerUser)
        ->patch("/occasions/{$occasion->slug}", [
            'title' => 'Hacked Title',
            'type' => $occasion->type->value,
        ])
        ->assertForbidden();

    expect($occasion->fresh()->title)->not->toBe('Hacked Title');
});

it('rejects editing an archived occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)
        ->patch("/occasions/{$occasion->slug}", [
            'title' => 'New Title',
            'type' => $occasion->type->value,
        ])
        ->assertSessionHasErrors('status');

    expect($occasion->fresh()->title)->not->toBe('New Title');
});

it('rejects an illegal status jump', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Draft]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)
        ->patch("/occasions/{$occasion->slug}", [
            'title' => $occasion->title,
            'type' => $occasion->type->value,
            'status' => OccasionStatus::Completed->value,
        ])
        ->assertSessionHasErrors('status');

    expect($occasion->fresh()->status)->toBe(OccasionStatus::Draft);
});

it('lets the host advance the occasion one legal stage at a time', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Draft]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)
        ->patch("/occasions/{$occasion->slug}", [
            'title' => $occasion->title,
            'type' => $occasion->type->value,
            'status' => OccasionStatus::Planning->value,
        ])
        ->assertSessionHasNoErrors();

    expect($occasion->fresh()->status)->toBe(OccasionStatus::Planning);
});
