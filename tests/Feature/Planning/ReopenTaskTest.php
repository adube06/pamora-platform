<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Enums\TaskStatus;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;

it('lets an authorized member reopen a completed task', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create([
        'occasion_id' => $occasion->id,
        'status' => TaskStatus::Completed,
        'completed_at' => now(),
    ]);

    $response = $this->actingAs($host)->post("/tasks/{$task->uuid}/reopen");

    $response->assertSessionHasNoErrors();

    $task->refresh();
    expect($task->status)->toBe(TaskStatus::Open)
        ->and($task->completed_at)->toBeNull();
});

it('rejects reopening a task that is not completed', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Open]);

    $this->actingAs($host)
        ->post("/tasks/{$task->uuid}/reopen")
        ->assertSessionHasErrors('status');
});

it('prevents a member without planning.reopen_task from reopening a task', function () {
    $occasion = Occasion::factory()->create();
    $task = Task::factory()->create([
        'occasion_id' => $occasion->id,
        'status' => TaskStatus::Completed,
        'completed_at' => now(),
    ]);

    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($guestUser)
        ->post("/tasks/{$task->uuid}/reopen")
        ->assertForbidden();

    expect($task->fresh()->status)->toBe(TaskStatus::Completed);
});

it('rejects reopening a task on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create([
        'occasion_id' => $occasion->id,
        'status' => TaskStatus::Completed,
        'completed_at' => now(),
    ]);

    $this->actingAs($host)
        ->post("/tasks/{$task->uuid}/reopen")
        ->assertSessionHasErrors('occasion');

    expect($task->fresh()->status)->toBe(TaskStatus::Completed);
});
