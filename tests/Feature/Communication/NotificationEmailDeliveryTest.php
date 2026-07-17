<?php

use App\Domains\Communication\Infrastructure\Mail\NotificationMail;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('emails the assignee when a task is assigned to someone else', function () {
    Mail::fake();

    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $assigneeUser = User::factory()->create();
    $assignee = OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $assigneeUser->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->post("/tasks/{$task->uuid}/assign", ['assignee_id' => $assignee->id]);

    Mail::assertSent(NotificationMail::class, fn ($mail) => $mail->hasTo($assigneeUser->email) && $mail->notification->type === 'task_assigned');
});

it('emails the host when a non-host member records a contribution', function () {
    Mail::fake();

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

    Mail::assertSent(NotificationMail::class, fn ($mail) => $mail->hasTo($host->email) && $mail->notification->type === 'contribution_received');
});

it('does not email a user who has disabled that notification type', function () {
    Mail::fake();

    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $assigneeUser = User::factory()->create(['notification_preferences' => ['task_assigned' => false]]);
    $assignee = OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $assigneeUser->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->post("/tasks/{$task->uuid}/assign", ['assignee_id' => $assignee->id]);

    Mail::assertNothingSent();
});
