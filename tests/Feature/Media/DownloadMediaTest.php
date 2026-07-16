<?php

use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('lets the uploader download their own media asset', function () {
    Storage::fake('local');
    Storage::disk('local')->put('media/1/venue.jpg', 'fake-image-bytes');

    $uploader = User::factory()->create();
    $occasion = Occasion::factory()->create();
    OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $uploader->id]);
    $mediaAsset = MediaAsset::factory()->create([
        'occasion_id' => $occasion->id,
        'attachable_id' => $occasion->id,
        'path' => 'media/1/venue.jpg',
        'uploaded_by' => $uploader->id,
        'visibility' => 'private',
    ]);

    $this->actingAs($uploader)
        ->get("/media/{$mediaAsset->uuid}/download")
        ->assertOk();
});

it('lets another active member download an occasion_members-visibility asset', function () {
    Storage::fake('local');
    Storage::disk('local')->put('media/1/venue.jpg', 'fake-image-bytes');

    $uploader = User::factory()->create();
    $viewer = User::factory()->create();
    $occasion = Occasion::factory()->create();
    OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $uploader->id]);
    OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $viewer->id]);
    $mediaAsset = MediaAsset::factory()->create([
        'occasion_id' => $occasion->id,
        'attachable_id' => $occasion->id,
        'path' => 'media/1/venue.jpg',
        'uploaded_by' => $uploader->id,
        'visibility' => 'occasion_members',
    ]);

    $this->actingAs($viewer)
        ->get("/media/{$mediaAsset->uuid}/download")
        ->assertOk();
});

it('prevents another member from downloading a private media asset that is not theirs', function () {
    Storage::fake('local');
    Storage::disk('local')->put('media/1/venue.jpg', 'fake-image-bytes');

    $uploader = User::factory()->create();
    $otherMember = User::factory()->create();
    $occasion = Occasion::factory()->create();
    OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $uploader->id]);
    OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $otherMember->id]);
    $mediaAsset = MediaAsset::factory()->create([
        'occasion_id' => $occasion->id,
        'attachable_id' => $occasion->id,
        'path' => 'media/1/venue.jpg',
        'uploaded_by' => $uploader->id,
        'visibility' => 'private',
    ]);

    $this->actingAs($otherMember)
        ->get("/media/{$mediaAsset->uuid}/download")
        ->assertForbidden();
});
