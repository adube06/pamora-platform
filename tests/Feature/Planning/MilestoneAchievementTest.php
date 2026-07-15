<?php

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Enums\TaskStatus;
use App\Domains\Planning\Domain\Models\Milestone;
use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Models\User;

it('is not achieved until every linked task is completed, then achieves automatically (BR-017)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $taskOne = Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Open]);
    $taskTwo = Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Open]);

    $milestone = Milestone::factory()->create(['occasion_id' => $occasion->id]);
    $milestone->tasks()->sync([$taskOne->id, $taskTwo->id]);

    expect($milestone->fresh(['tasks'])->isAchieved())->toBeFalse();

    $this->actingAs($host)->post("/tasks/{$taskOne->uuid}/complete");

    expect($milestone->fresh(['tasks'])->isAchieved())->toBeFalse();
    expect(ActivityLog::where('action', 'planning.milestone_completed')->count())->toBe(0);

    $this->actingAs($host)->post("/tasks/{$taskTwo->uuid}/complete");

    expect($milestone->fresh(['tasks'])->isAchieved())->toBeTrue();
    expect(ActivityLog::where('action', 'planning.milestone_completed')->where('subject_id', $milestone->id)->count())->toBe(1);
});

it('un-achieves silently when a linked task is reopened, with no new log entry', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $task = Task::factory()->create([
        'occasion_id' => $occasion->id,
        'status' => TaskStatus::Completed,
        'completed_at' => now(),
    ]);

    $milestone = Milestone::factory()->create(['occasion_id' => $occasion->id]);
    $milestone->tasks()->sync([$task->id]);

    expect($milestone->fresh(['tasks'])->isAchieved())->toBeTrue();

    $this->actingAs($host)->post("/tasks/{$task->uuid}/reopen");

    expect($milestone->fresh(['tasks'])->isAchieved())->toBeFalse();
    expect(ActivityLog::where('action', 'planning.milestone_completed')->count())->toBe(0);
});

it('is not achieved when it has no linked tasks', function () {
    $milestone = Milestone::factory()->create();

    expect($milestone->fresh(['tasks'])->isAchieved())->toBeFalse();
});
