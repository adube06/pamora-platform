<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\Checklist;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;

it('lets an authorized member update a task\'s fields', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'title' => 'Book Venue', 'priority' => 'low']);
    $checklist = Checklist::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->patch("/tasks/{$task->uuid}", [
            'title' => 'Book the Venue',
            'description' => 'Confirm with the venue by Friday',
            'priority' => 'high',
            'due_date' => now()->addWeek()->toDateString(),
            'checklist_id' => $checklist->id,
        ])
        ->assertSessionHasNoErrors();

    $task->refresh();
    expect($task->title)->toBe('Book the Venue')
        ->and($task->description)->toBe('Confirm with the venue by Friday')
        ->and($task->priority->value)->toBe('high')
        ->and($task->checklist_id)->toBe($checklist->id);
});

it('rejects a checklist_id belonging to a different occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'title' => 'Book Venue']);
    $foreignChecklist = Checklist::factory()->create();

    $this->actingAs($host)
        ->patch("/tasks/{$task->uuid}", ['title' => 'Book Venue', 'checklist_id' => $foreignChecklist->id])
        ->assertSessionHasErrors('checklist_id');

    expect($task->fresh()->checklist_id)->toBeNull();
});

it('rejects updating a task on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'title' => 'Book Venue']);

    $this->actingAs($host)
        ->patch("/tasks/{$task->uuid}", ['title' => 'Should not update'])
        ->assertSessionHasErrors('occasion');

    expect($task->fresh()->title)->toBe('Book Venue');
});

it('prevents a member without planning.edit_task from updating a task', function () {
    $occasion = Occasion::factory()->create();
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'title' => 'Book Venue']);
    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($guestUser)
        ->patch("/tasks/{$task->uuid}", ['title' => 'Should not update'])
        ->assertForbidden();

    expect($task->fresh()->title)->toBe('Book Venue');
});
