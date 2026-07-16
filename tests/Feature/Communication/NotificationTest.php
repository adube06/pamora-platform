<?php

use App\Domains\Communication\Domain\Models\Notification;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\Invitation;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Enums\TaskStatus;
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

it('creates a notification for the task creator when someone else completes it', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $creator = User::factory()->create();
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Open, 'created_by' => $creator->id]);

    $this->actingAs($host)->post("/tasks/{$task->uuid}/complete");

    $notification = Notification::where('user_id', $creator->id)->first();

    expect($notification)->not->toBeNull()
        ->and($notification->type)->toBe('task_completed')
        ->and($notification->subject_type)->toBe('Task')
        ->and($notification->subject_id)->toBe($task->id);
});

it('does not create a notification when the task creator completes their own task', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'status' => TaskStatus::Open, 'created_by' => $host->id]);

    $this->actingAs($host)->post("/tasks/{$task->uuid}/complete");

    expect(Notification::where('user_id', $host->id)->exists())->toBeFalse();
});

it('creates a notification for the host when a non-host member records a contribution', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $recorder = User::factory()->create();
    OccasionMember::factory()->role(Role::Treasurer)->create(['occasion_id' => $occasion->id, 'user_id' => $recorder->id]);

    $this->actingAs($recorder)->post("/occasions/{$occasion->slug}/contributions", [
        'contributor_name' => 'Amina Hassan',
        'amount' => 50000,
        'method' => 'cash',
        'contributed_at' => now()->toDateString(),
    ]);

    $notification = Notification::where('user_id', $host->id)->first();

    expect($notification)->not->toBeNull()
        ->and($notification->type)->toBe('contribution_received')
        ->and($notification->subject_type)->toBe('Contribution');
});

it('does not create a notification when the host records their own contribution', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/contributions", [
        'contributor_name' => 'Amina Hassan',
        'amount' => 50000,
        'method' => 'cash',
        'contributed_at' => now()->toDateString(),
    ]);

    expect(Notification::where('user_id', $host->id)->exists())->toBeFalse();
});

it('creates a notification for the host when an invited member joins', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/committee/invitations", [
        'email' => 'invitee@example.com',
        'role' => 'member',
    ]);

    $invitation = Invitation::firstWhere('email', 'invitee@example.com');
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    $this->actingAs($invitee)->post("/invitations/{$invitation->token}/accept");

    $notification = Notification::where('user_id', $host->id)->first();

    expect($notification)->not->toBeNull()
        ->and($notification->type)->toBe('member_joined')
        ->and($notification->subject_type)->toBe('OccasionMember');
});

it('does not notify the host about their own initial membership when an occasion is created', function () {
    $host = User::factory()->create();

    $this->actingAs($host)->post('/occasions', ['title' => 'Test Occasion', 'type' => 'wedding']);

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
