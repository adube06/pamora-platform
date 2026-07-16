<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Enums\TaskStatus;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;

it('lets an authorized member add a dependency to a task', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $dependsOnTask = Task::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->post("/tasks/{$task->uuid}/dependencies", ['depends_on_task_id' => $dependsOnTask->id])
        ->assertSessionHasNoErrors();

    expect($task->dependencies()->where('depends_on_task_id', $dependsOnTask->id)->exists())->toBeTrue()
        ->and($task->fresh(['dependencies'])->isBlocked())->toBeTrue();

    $dependsOnTask->update(['status' => TaskStatus::Completed]);

    expect($task->fresh(['dependencies'])->isBlocked())->toBeFalse();
});

it('rejects a task depending on itself', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->post("/tasks/{$task->uuid}/dependencies", ['depends_on_task_id' => $task->id])
        ->assertSessionHasErrors('depends_on_task_id');

    expect($task->dependencies()->count())->toBe(0);
});

it('rejects a depends_on_task_id belonging to a different occasion', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $foreignTask = Task::factory()->create();

    $this->actingAs($host)
        ->post("/tasks/{$task->uuid}/dependencies", ['depends_on_task_id' => $foreignTask->id])
        ->assertSessionHasErrors('depends_on_task_id');

    expect($task->dependencies()->count())->toBe(0);
});

it('rejects a duplicate dependency', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $dependsOnTask = Task::factory()->create(['occasion_id' => $occasion->id]);
    $task->dependencies()->attach($dependsOnTask->id);

    $this->actingAs($host)
        ->post("/tasks/{$task->uuid}/dependencies", ['depends_on_task_id' => $dependsOnTask->id])
        ->assertSessionHasErrors('depends_on_task_id');

    expect($task->dependencies()->count())->toBe(1);
});

it('rejects a direct circular dependency', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $taskA = Task::factory()->create(['occasion_id' => $occasion->id]);
    $taskB = Task::factory()->create(['occasion_id' => $occasion->id]);
    $taskA->dependencies()->attach($taskB->id);

    $this->actingAs($host)
        ->post("/tasks/{$taskB->uuid}/dependencies", ['depends_on_task_id' => $taskA->id])
        ->assertSessionHasErrors('depends_on_task_id');

    expect($taskB->dependencies()->count())->toBe(0);
});

it('rejects a transitive circular dependency', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $taskA = Task::factory()->create(['occasion_id' => $occasion->id]);
    $taskB = Task::factory()->create(['occasion_id' => $occasion->id]);
    $taskC = Task::factory()->create(['occasion_id' => $occasion->id]);
    $taskA->dependencies()->attach($taskB->id);
    $taskB->dependencies()->attach($taskC->id);

    $this->actingAs($host)
        ->post("/tasks/{$taskC->uuid}/dependencies", ['depends_on_task_id' => $taskA->id])
        ->assertSessionHasErrors('depends_on_task_id');

    expect($taskC->dependencies()->count())->toBe(0);
});

it('rejects adding a dependency on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $dependsOnTask = Task::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->post("/tasks/{$task->uuid}/dependencies", ['depends_on_task_id' => $dependsOnTask->id])
        ->assertSessionHasErrors('occasion');

    expect($task->dependencies()->count())->toBe(0);
});

it('prevents a member without planning.edit_task from adding a dependency', function () {
    $occasion = Occasion::factory()->create();
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $dependsOnTask = Task::factory()->create(['occasion_id' => $occasion->id]);
    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($guestUser)
        ->post("/tasks/{$task->uuid}/dependencies", ['depends_on_task_id' => $dependsOnTask->id])
        ->assertForbidden();

    expect($task->dependencies()->count())->toBe(0);
});
