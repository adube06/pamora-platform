<?php

use App\Domains\Media\Domain\Models\Album;
use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets an authorized member move a media asset into an album', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $album = Album::factory()->create(['occasion_id' => $occasion->id]);
    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);

    $response = $this->actingAs($host)->patch("/media/{$mediaAsset->uuid}/move", [
        'album_id' => $album->id,
    ]);

    $response->assertSessionHasNoErrors();

    $mediaAsset->refresh();
    expect($mediaAsset->attachable_type)->toBe(Album::class)
        ->and($mediaAsset->attachable_id)->toBe($album->id);
});

it('lets an authorized member move a media asset back to the occasion gallery', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $album = Album::factory()->create(['occasion_id' => $occasion->id]);
    $mediaAsset = MediaAsset::factory()->create([
        'occasion_id' => $occasion->id,
        'attachable_type' => Album::class,
        'attachable_id' => $album->id,
    ]);

    $this->actingAs($host)
        ->patch("/media/{$mediaAsset->uuid}/move", ['album_id' => null])
        ->assertSessionHasNoErrors();

    $mediaAsset->refresh();
    expect($mediaAsset->attachable_type)->toBe(Occasion::class)
        ->and($mediaAsset->attachable_id)->toBe($occasion->id);
});

it('rejects an album that belongs to a different occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);
    $otherOccasionAlbum = Album::factory()->create();

    $this->actingAs($host)
        ->patch("/media/{$mediaAsset->uuid}/move", ['album_id' => $otherOccasionAlbum->id])
        ->assertSessionHasErrors('album_id');
});

it('prevents a member without media.edit_metadata from moving a media asset', function () {
    $occasion = Occasion::factory()->create();
    $album = Album::factory()->create(['occasion_id' => $occasion->id]);
    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);
    $observerUser = User::factory()->create();
    OccasionMember::factory()->role(Role::Observer)->create([
        'occasion_id' => $occasion->id,
        'user_id' => $observerUser->id,
    ]);

    $this->actingAs($observerUser)
        ->patch("/media/{$mediaAsset->uuid}/move", ['album_id' => $album->id])
        ->assertForbidden();

    expect($mediaAsset->fresh()->attachable_type)->toBe(Occasion::class);
});
