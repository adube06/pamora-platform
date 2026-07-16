<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;

it('lets an authorized member remove a dependency from a task', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $dependsOnTask = Task::factory()->create(['occasion_id' => $occasion->id]);
    $task->dependencies()->attach($dependsOnTask->id);

    $this->actingAs($host)
        ->delete("/tasks/{$task->uuid}/dependencies/{$dependsOnTask->uuid}")
        ->assertSessionHasNoErrors();

    expect($task->dependencies()->count())->toBe(0);
});

it('rejects removing a dependency pair that does not exist', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $unrelatedTask = Task::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)
        ->delete("/tasks/{$task->uuid}/dependencies/{$unrelatedTask->uuid}")
        ->assertSessionHasErrors('depends_on_task_id');
});

it('rejects removing a dependency on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $dependsOnTask = Task::factory()->create(['occasion_id' => $occasion->id]);
    $task->dependencies()->attach($dependsOnTask->id);

    $this->actingAs($host)
        ->delete("/tasks/{$task->uuid}/dependencies/{$dependsOnTask->uuid}")
        ->assertSessionHasErrors('occasion');

    expect($task->dependencies()->count())->toBe(1);
});

it('prevents a member without planning.edit_task from removing a dependency', function () {
    $occasion = Occasion::factory()->create();
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $dependsOnTask = Task::factory()->create(['occasion_id' => $occasion->id]);
    $task->dependencies()->attach($dependsOnTask->id);

    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $this->actingAs($guestUser)
        ->delete("/tasks/{$task->uuid}/dependencies/{$dependsOnTask->uuid}")
        ->assertForbidden();

    expect($task->dependencies()->count())->toBe(1);
});
