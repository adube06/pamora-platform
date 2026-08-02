<?php

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Enums\TaskStatus;
use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function occasionWithHostPlanning(): array
{
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'created_by' => $host->id]);
    $hostMember = OccasionMember::factory()->host()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $host->id,
    ]);

    return [$occasion, $host, $hostMember];
}

it('lists tasks with dependency data via the API', function () {
    [$occasion, $host] = occasionWithHostPlanning();
    $blocker = Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Open]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $task->dependencies()->attach($blocker->id);

    Sanctum::actingAs($host);

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/tasks");

    $response->assertOk();
    $ids = collect($response->json('data'))->keyBy('id');
    expect($ids[$task->uuid]['is_blocked'])->toBeTrue()
        ->and($ids[$task->uuid]['dependencies'])->toHaveCount(1);
});

it('rejects listing tasks for a non-member', function () {
    [$occasion] = occasionWithHostPlanning();
    Sanctum::actingAs(User::factory()->create());

    $response = $this->getJson("/api/v1/occasions/{$occasion->slug}/tasks");

    $response->assertStatus(403);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
});

it('creates a task via the API with the default priority when omitted', function () {
    [$occasion, $host] = occasionWithHostPlanning();
    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/tasks", ['title' => 'Book the venue']);

    $response->assertCreated();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.priority', 'medium');
});

it('rejects creating a task on an archived occasion via the API', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'created_by' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/occasions/{$occasion->slug}/tasks", ['title' => 'Should not be created']);

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('logs an audit entry when a task is created via the API', function () {
    [$occasion, $host] = occasionWithHostPlanning();
    Sanctum::actingAs($host);

    $this->postJson("/api/v1/occasions/{$occasion->slug}/tasks", ['title' => 'Audited Task']);

    $task = Task::firstWhere('title', 'Audited Task');

    expect(ActivityLog::where('action', 'planning.task_created')
        ->where('subject_id', $task->id)
        ->count())->toBe(1);
});

it('lets an authorized member update a task via the API', function () {
    [$occasion, $host] = occasionWithHostPlanning();
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'title' => 'Old']);

    Sanctum::actingAs($host);

    $response = $this->patchJson("/api/v1/tasks/{$task->uuid}", ['title' => 'New']);

    $response->assertOk();
    expect($task->fresh()->title)->toBe('New');
});

it('lets an authorized member assign a task to an occasion member via the API', function () {
    [$occasion, $host] = occasionWithHostPlanning();
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $assignee = OccasionMember::factory()->create(['occasion_id' => $occasion->id]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/tasks/{$task->uuid}/assign", ['assignee_id' => $assignee->id]);

    $response->assertOk();
    $response->assertJsonPath('data.assignee_id', $assignee->uuid);
});

it('rejects assigning a task to a member from a different occasion', function () {
    [$occasion, $host] = occasionWithHostPlanning();
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $foreignAssignee = OccasionMember::factory()->create();

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/tasks/{$task->uuid}/assign", ['assignee_id' => $foreignAssignee->id]);

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('lets an authorized member complete a task via the API', function () {
    [$occasion, $host] = occasionWithHostPlanning();
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Open]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/tasks/{$task->uuid}/complete");

    $response->assertOk();
    $response->assertJsonPath('data.status', 'completed');
});

it('rejects completing an already-completed task via the API', function () {
    [$occasion, $host] = occasionWithHostPlanning();
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Completed]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/tasks/{$task->uuid}/complete");

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('lets an authorized member reopen a completed task via the API', function () {
    [$occasion, $host] = occasionWithHostPlanning();
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Completed, 'completed_at' => now()]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/tasks/{$task->uuid}/reopen");

    $response->assertOk();
    $response->assertJsonPath('data.status', 'open');
});

it('rejects reopening a task that is not completed via the API', function () {
    [$occasion, $host] = occasionWithHostPlanning();
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Open]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/tasks/{$task->uuid}/reopen");

    $response->assertStatus(422);
});

it('lets an authorized member add a task dependency via the API', function () {
    [$occasion, $host] = occasionWithHostPlanning();
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $dependsOn = Task::factory()->create(['occasion_id' => $occasion->id]);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/tasks/{$task->uuid}/dependencies", ['depends_on_task_id' => $dependsOn->id]);

    $response->assertCreated();
    $response->assertJsonPath('data.is_blocked', true);
});

it('rejects a dependency that would create a cycle via the API', function () {
    [$occasion, $host] = occasionWithHostPlanning();
    $taskA = Task::factory()->create(['occasion_id' => $occasion->id]);
    $taskB = Task::factory()->create(['occasion_id' => $occasion->id]);
    $taskA->dependencies()->attach($taskB->id);

    Sanctum::actingAs($host);

    $response = $this->postJson("/api/v1/tasks/{$taskB->uuid}/dependencies", ['depends_on_task_id' => $taskA->id]);

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('lets an authorized member remove a task dependency via the API', function () {
    [$occasion, $host] = occasionWithHostPlanning();
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $dependsOn = Task::factory()->create(['occasion_id' => $occasion->id]);
    $task->dependencies()->attach($dependsOn->id);

    Sanctum::actingAs($host);

    $response = $this->deleteJson("/api/v1/tasks/{$task->uuid}/dependencies/{$dependsOn->uuid}");

    $response->assertOk();
    $response->assertJsonPath('data.is_blocked', false);
});
