<?php

use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('lets an authorized member upload a media asset', function () {
    Storage::fake('local');

    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $file = UploadedFile::fake()->image('venue.jpg', 100, 100);

    $response = $this->actingAs($host)->post("/occasions/{$occasion->slug}/media", [
        'file' => $file,
    ]);

    $response->assertSessionHasNoErrors();

    $mediaAsset = MediaAsset::firstWhere('file_name', 'venue.jpg');

    expect($mediaAsset)->not->toBeNull()
        ->and($mediaAsset->file_type->value)->toBe('image')
        ->and($mediaAsset->visibility)->toBe('occasion_members')
        ->and($mediaAsset->uploaded_by)->toBe($host->id);

    Storage::disk('local')->assertExists($mediaAsset->path);
});

it('prevents a member without media.upload from uploading', function () {
    Storage::fake('local');

    $occasion = Occasion::factory()->create();
    $observerUser = User::factory()->create();
    OccasionMember::factory()->role(Role::Observer)->create([
        'occasion_id' => $occasion->id,
        'user_id' => $observerUser->id,
    ]);

    $file = UploadedFile::fake()->image('venue.jpg');

    $this->actingAs($observerUser)
        ->post("/occasions/{$occasion->slug}/media", ['file' => $file])
        ->assertForbidden();

    expect(MediaAsset::where('file_name', 'venue.jpg')->exists())->toBeFalse();
});

it('rejects a disallowed file type', function () {
    Storage::fake('local');

    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $file = UploadedFile::fake()->create('script.exe', 10);

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/media", ['file' => $file])
        ->assertSessionHasErrors('file');
});

it('rejects uploading media to an archived occasion (BR-009)', function () {
    Storage::fake('local');

    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $file = UploadedFile::fake()->image('venue.jpg');

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/media", ['file' => $file])
        ->assertSessionHasErrors('occasion');

    expect(MediaAsset::where('file_name', 'venue.jpg')->exists())->toBeFalse();
});
