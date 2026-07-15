<?php

use App\Domains\Finance\Domain\Models\Contribution;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\Invitation;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Models\User;

it('logs an entry when a user registers', function () {
    $this->post('/register', [
        'name' => 'Amina Hassan',
        'email' => 'amina@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    expect(ActivityLog::where('action', 'identity.user_registered')->count())->toBe(1);
});

it('logs an entry when an occasion is created', function () {
    $host = User::factory()->create();

    $this->actingAs($host)->post('/occasions', ['title' => 'Test Occasion', 'type' => 'wedding']);

    $occasion = Occasion::firstWhere('title', 'Test Occasion');

    expect(ActivityLog::where('action', 'occasion.created')
        ->where('occasion_id', $occasion->id)
        ->count())->toBe(1);

    // Creating the Occasion also creates the Host's membership, which is
    // its own auditable event.
    expect(ActivityLog::where('action', 'people.member_joined')->count())->toBe(1);
});

it('logs an entry when a member is invited and when they accept', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/committee/invitations", [
        'email' => 'invitee@example.com',
        'role' => 'member',
    ]);

    expect(ActivityLog::where('action', 'people.member_invited')->count())->toBe(1);

    $invitation = Invitation::firstWhere('email', 'invitee@example.com');
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    $this->actingAs($invitee)->post("/invitations/{$invitation->token}/accept");

    // The Host's membership above was created directly via factory (not
    // through CreateHostMembershipService), so only the invitee's
    // acceptance goes through AcceptInvitationService and dispatches
    // MemberJoined here.
    expect(ActivityLog::where('action', 'people.member_joined')->count())->toBe(1);
});

it('logs an entry when a task is created and when it is assigned', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $assignee = OccasionMember::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/tasks", ['title' => 'Book DJ']);

    $task = Task::firstWhere('title', 'Book DJ');

    expect(ActivityLog::where('action', 'planning.task_created')->where('subject_id', $task->id)->count())->toBe(1);

    $this->actingAs($host)->post("/tasks/{$task->uuid}/assign", ['assignee_id' => $assignee->id]);

    expect(ActivityLog::where('action', 'planning.task_assigned')->where('subject_id', $task->id)->count())->toBe(1);
});

it('logs an entry when a contribution is recorded', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/contributions", [
        'contributor_name' => 'Amina Hassan',
        'amount' => 50000,
        'method' => 'cash',
        'contributed_at' => now()->toDateString(),
    ]);

    $contribution = Contribution::firstWhere('contributor_name', 'Amina Hassan');

    $log = ActivityLog::where('action', 'finance.contribution_received')
        ->where('subject_id', $contribution->id)
        ->first();

    expect($log)->not->toBeNull()
        // Guards against the currency being blank in the description because
        // the in-memory model wasn't refreshed after relying on the DB-level
        // default (currency is not a validated input field this slice).
        ->and($log->description)->toContain('TZS');
});
