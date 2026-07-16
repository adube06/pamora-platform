<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;

it('assigns a task to a member of the same occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    $hostMember = OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $assignee = OccasionMember::factory()->create(['occasion_id' => $occasion->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);

    $response = $this->actingAs($host)->post("/tasks/{$task->uuid}/assign", [
        'assignee_id' => $assignee->id,
    ]);

    $response->assertSessionHasNoErrors();

    expect($task->fresh()->assignee_id)->toBe($assignee->id);
});

it('rejects assigning a task to a member of a different occasion (BR-015)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);

    $otherOccasionMember = OccasionMember::factory()->create();

    $this->actingAs($host)
        ->post("/tasks/{$task->uuid}/assign", ['assignee_id' => $otherOccasionMember->id])
        ->assertSessionHasErrors('assignee_id');

    expect($task->fresh()->assignee_id)->toBeNull();
});

it('prevents a member without planning.assign_task from assigning a task', function () {
    $occasion = Occasion::factory()->create();
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $assignee = OccasionMember::factory()->create(['occasion_id' => $occasion->id]);

    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($guestUser)
        ->post("/tasks/{$task->uuid}/assign", ['assignee_id' => $assignee->id])
        ->assertForbidden();
});

it('rejects assigning a task on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $assignee = OccasionMember::factory()->create(['occasion_id' => $occasion->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->post("/tasks/{$task->uuid}/assign", ['assignee_id' => $assignee->id])
        ->assertSessionHasErrors('occasion');

    expect($task->fresh()->assignee_id)->toBeNull();
});
