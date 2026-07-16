<?php

use App\Domains\Communication\Domain\Models\Announcement;
use App\Domains\Finance\Domain\Models\Expense;
use App\Domains\Media\Domain\Models\Album;
use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\Task;
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

it('lets an authorized member attach a media asset to a task', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);

    $response = $this->actingAs($host)->patch("/media/{$mediaAsset->uuid}/move", [
        'task_id' => $task->id,
    ]);

    $response->assertSessionHasNoErrors();

    $mediaAsset->refresh();
    expect($mediaAsset->attachable_type)->toBe(Task::class)
        ->and($mediaAsset->attachable_id)->toBe($task->id);
});

it('rejects a task that belongs to a different occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);
    $otherOccasionTask = Task::factory()->create();

    $this->actingAs($host)
        ->patch("/media/{$mediaAsset->uuid}/move", ['task_id' => $otherOccasionTask->id])
        ->assertSessionHasErrors('task_id');
});

it('lets an authorized member attach a media asset to an expense', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $expense = Expense::factory()->create(['occasion_id' => $occasion->id]);
    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);

    $response = $this->actingAs($host)->patch("/media/{$mediaAsset->uuid}/move", [
        'expense_id' => $expense->id,
    ]);

    $response->assertSessionHasNoErrors();

    $mediaAsset->refresh();
    expect($mediaAsset->attachable_type)->toBe(Expense::class)
        ->and($mediaAsset->attachable_id)->toBe($expense->id);
});

it('rejects an expense that belongs to a different occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);
    $otherOccasionExpense = Expense::factory()->create();

    $this->actingAs($host)
        ->patch("/media/{$mediaAsset->uuid}/move", ['expense_id' => $otherOccasionExpense->id])
        ->assertSessionHasErrors('expense_id');
});

it('lets an authorized member attach a media asset to an announcement', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $announcement = Announcement::factory()->create(['occasion_id' => $occasion->id]);
    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);

    $response = $this->actingAs($host)->patch("/media/{$mediaAsset->uuid}/move", [
        'announcement_id' => $announcement->id,
    ]);

    $response->assertSessionHasNoErrors();

    $mediaAsset->refresh();
    expect($mediaAsset->attachable_type)->toBe(Announcement::class)
        ->and($mediaAsset->attachable_id)->toBe($announcement->id);
});

it('rejects an announcement that belongs to a different occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);
    $otherOccasionAnnouncement = Announcement::factory()->create();

    $this->actingAs($host)
        ->patch("/media/{$mediaAsset->uuid}/move", ['announcement_id' => $otherOccasionAnnouncement->id])
        ->assertSessionHasErrors('announcement_id');
});

it('rejects a request providing both an expense_id and an announcement_id', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $expense = Expense::factory()->create(['occasion_id' => $occasion->id]);
    $announcement = Announcement::factory()->create(['occasion_id' => $occasion->id]);
    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->patch("/media/{$mediaAsset->uuid}/move", ['expense_id' => $expense->id, 'announcement_id' => $announcement->id])
        ->assertSessionHasErrors('album_id');

    expect($mediaAsset->fresh()->attachable_type)->toBe(Occasion::class);
});

it('rejects a request providing both a task_id and an expense_id', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $expense = Expense::factory()->create(['occasion_id' => $occasion->id]);
    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->patch("/media/{$mediaAsset->uuid}/move", ['task_id' => $task->id, 'expense_id' => $expense->id])
        ->assertSessionHasErrors('album_id');

    expect($mediaAsset->fresh()->attachable_type)->toBe(Occasion::class);
});

it('rejects a request providing both an album_id and a task_id', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $album = Album::factory()->create(['occasion_id' => $occasion->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->patch("/media/{$mediaAsset->uuid}/move", ['album_id' => $album->id, 'task_id' => $task->id])
        ->assertSessionHasErrors('album_id');

    expect($mediaAsset->fresh()->attachable_type)->toBe(Occasion::class);
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

it('rejects moving a media asset on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $album = Album::factory()->create(['occasion_id' => $occasion->id]);
    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->patch("/media/{$mediaAsset->uuid}/move", ['album_id' => $album->id])
        ->assertSessionHasErrors('occasion');

    expect($mediaAsset->fresh()->attachable_type)->toBe(Occasion::class);
});
