<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Enums\TaskStatus;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;

it('lets an authorized member complete an open task', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Open]);

    $response = $this->actingAs($host)->post("/tasks/{$task->uuid}/complete");

    $response->assertSessionHasNoErrors();

    $task->refresh();
    expect($task->status)->toBe(TaskStatus::Completed)
        ->and($task->completed_at)->not->toBeNull();
});

it('rejects completing an already-completed task', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create([
        'occasion_id' => $occasion->id,
        'status' => TaskStatus::Completed,
        'completed_at' => now(),
    ]);

    $this->actingAs($host)
        ->post("/tasks/{$task->uuid}/complete")
        ->assertSessionHasErrors('status');
});

it('rejects completing a cancelled task', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Cancelled]);

    $this->actingAs($host)
        ->post("/tasks/{$task->uuid}/complete")
        ->assertSessionHasErrors('status');
});

it('prevents a member without planning.complete_task from completing a task', function () {
    $occasion = Occasion::factory()->create();
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Open]);

    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($guestUser)
        ->post("/tasks/{$task->uuid}/complete")
        ->assertForbidden();

    expect($task->fresh()->status)->toBe(TaskStatus::Open);
});

it('rejects completing a task on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Open]);

    $this->actingAs($host)
        ->post("/tasks/{$task->uuid}/complete")
        ->assertSessionHasErrors('occasion');

    expect($task->fresh()->status)->toBe(TaskStatus::Open);
});
