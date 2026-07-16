<?php

use App\Domains\Media\Domain\Models\Album;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets an authorized member create an album', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $response = $this->actingAs($host)->post("/occasions/{$occasion->slug}/albums", [
        'name' => 'Ceremony',
    ]);

    $response->assertSessionHasNoErrors();

    expect(Album::firstWhere('name', 'Ceremony'))->not->toBeNull();
});

it('prevents a member without media.upload from creating an album', function () {
    $occasion = Occasion::factory()->create();
    $observerUser = User::factory()->create();
    OccasionMember::factory()->role(Role::Observer)->create([
        'occasion_id' => $occasion->id,
        'user_id' => $observerUser->id,
    ]);

    $this->actingAs($observerUser)
        ->post("/occasions/{$occasion->slug}/albums", ['name' => 'Should not be created'])
        ->assertForbidden();

    expect(Album::where('name', 'Should not be created')->exists())->toBeFalse();
});
