<?php

use App\Domains\Communication\Domain\Models\Notification;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Models\User;

it('lets a user update their notification preferences and logs the change', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/preferences', ['task_assigned' => false])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->wantsNotification('task_assigned'))->toBeFalse()
        ->and($user->fresh()->wantsNotification('task_completed'))->toBeTrue();

    expect(ActivityLog::where('action', 'communication.preferences_updated')
        ->where('subject_id', $user->id)
        ->count())->toBe(1);
});

it('suppresses a notification for a type the recipient has disabled', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $assigneeUser = User::factory()->create();
    $assignee = OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $assigneeUser->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($assigneeUser)->patch('/preferences', ['task_assigned' => false]);

    $this->actingAs($host)->post("/tasks/{$task->uuid}/assign", ['assignee_id' => $assignee->id]);

    expect(Notification::where('user_id', $assigneeUser->id)->where('type', 'task_assigned')->exists())->toBeFalse();
});

it('still creates a notification for a type left at its default enabled state', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $assigneeUser = User::factory()->create();
    $assignee = OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $assigneeUser->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);

    // Disable an unrelated type only.
    $this->actingAs($assigneeUser)->patch('/preferences', ['member_joined' => false]);

    $this->actingAs($host)->post("/tasks/{$task->uuid}/assign", ['assignee_id' => $assignee->id]);

    expect(Notification::where('user_id', $assigneeUser->id)->where('type', 'task_assigned')->exists())->toBeTrue();
});
