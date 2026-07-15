<?php

use App\Domains\Communication\Domain\Models\Notification;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;

it('creates a notification for the assignee when a task is assigned to someone else', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $assigneeUser = User::factory()->create();
    $assignee = OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $assigneeUser->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->post("/tasks/{$task->uuid}/assign", ['assignee_id' => $assignee->id]);

    $notification = Notification::where('user_id', $assigneeUser->id)->first();

    expect($notification)->not->toBeNull()
        ->and($notification->type)->toBe('task_assigned')
        ->and($notification->subject_type)->toBe('Task')
        ->and($notification->subject_id)->toBe($task->id)
        ->and($notification->read_at)->toBeNull();
});

it('does not create a notification when a member assigns a task to themselves', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    $hostMember = OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->post("/tasks/{$task->uuid}/assign", ['assignee_id' => $hostMember->id]);

    expect(Notification::where('user_id', $host->id)->exists())->toBeFalse();
});

it('lets the recipient mark their own notification as read', function () {
    $recipient = User::factory()->create();
    $notification = Notification::factory()->create(['user_id' => $recipient->id]);

    $this->actingAs($recipient)
        ->post("/notifications/{$notification->uuid}/read")
        ->assertSessionHasNoErrors();

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('prevents a user from marking someone else\'s notification as read', function () {
    $recipient = User::factory()->create();
    $notification = Notification::factory()->create(['user_id' => $recipient->id]);

    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->post("/notifications/{$notification->uuid}/read")
        ->assertForbidden();

    expect($notification->fresh()->read_at)->toBeNull();
});
