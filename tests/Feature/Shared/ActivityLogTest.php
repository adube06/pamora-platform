<?php

use App\Domains\Communication\Domain\Models\Announcement;
use App\Domains\Communication\Domain\Models\ReminderRule;
use App\Domains\Finance\Domain\Models\Budget;
use App\Domains\Finance\Domain\Models\BudgetCategory;
use App\Domains\Finance\Domain\Models\Contribution;
use App\Domains\Finance\Domain\Models\Expense;
use App\Domains\Media\Domain\Models\Album;
use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\Invitation;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Models\Checklist;
use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Planning\Domain\Models\TimelineEvent;
use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

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

it('logs an entry when an rsvp is submitted and when it is reopened', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $guestUser = User::factory()->create();
    $guestMember = OccasionMember::factory()->create(['occasion_id' => $occasion->id, 'user_id' => $guestUser->id]);

    $this->actingAs($guestUser)->post("/occasions/{$occasion->slug}/rsvp", ['rsvp_status' => 'attending']);

    expect(ActivityLog::where('action', 'people.rsvp_submitted')
        ->where('subject_id', $guestMember->id)
        ->count())->toBe(1);

    $this->actingAs($host)->post("/occasion-members/{$guestMember->uuid}/reopen-rsvp");

    expect(ActivityLog::where('action', 'people.rsvp_reopened')
        ->where('subject_id', $guestMember->id)
        ->count())->toBe(1);
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

it('logs an entry when a task is completed and when it is reopened', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->post("/tasks/{$task->uuid}/complete");

    expect(ActivityLog::where('action', 'planning.task_completed')->where('subject_id', $task->id)->count())->toBe(1);

    $this->actingAs($host)->post("/tasks/{$task->uuid}/reopen");

    expect(ActivityLog::where('action', 'planning.task_reopened')->where('subject_id', $task->id)->count())->toBe(1);
});

it('logs an entry when a checklist is created', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/checklists", ['name' => 'Catering']);

    $checklist = Checklist::firstWhere('name', 'Catering');

    expect(ActivityLog::where('action', 'planning.checklist_created')->where('subject_id', $checklist->id)->count())->toBe(1);
});

it('logs an entry when a timeline event is scheduled', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/timeline-events", [
        'name' => 'Committee Meeting',
        'scheduled_at' => now()->addWeek()->toDateTimeString(),
    ]);

    $timelineEvent = TimelineEvent::firstWhere('name', 'Committee Meeting');

    expect(ActivityLog::where('action', 'planning.timeline_event_scheduled')->where('subject_id', $timelineEvent->id)->count())->toBe(1);
});

it('logs an entry when an announcement is published', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/announcements", [
        'title' => 'Venue update',
        'message' => 'The venue has changed to the community hall.',
    ]);

    $announcement = Announcement::firstWhere('title', 'Venue update');

    expect(ActivityLog::where('action', 'communication.announcement_published')
        ->where('subject_id', $announcement->id)
        ->count())->toBe(1);
});

it('logs an entry when a reminder rule is scheduled and when it is triggered', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $timelineEvent = TimelineEvent::factory()->create([
        'occasion_id' => $occasion->id,
        'scheduled_at' => now()->addMinutes(30),
    ]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/reminder-rules", [
        'timeline_event_id' => $timelineEvent->id,
        'offset_minutes' => 120,
    ]);

    $rule = ReminderRule::firstWhere('timeline_event_id', $timelineEvent->id);

    expect(ActivityLog::where('action', 'communication.reminder_scheduled')
        ->where('subject_id', $rule->id)
        ->count())->toBe(1);

    Artisan::call('reminders:dispatch');

    expect(ActivityLog::where('action', 'communication.reminder_triggered')
        ->where('subject_id', $rule->id)
        ->count())->toBe(1);
});

it('logs an entry when a media asset is uploaded', function () {
    Storage::fake('local');

    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $file = UploadedFile::fake()->image('venue.jpg');

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/media", ['file' => $file]);

    $mediaAsset = MediaAsset::firstWhere('file_name', 'venue.jpg');

    expect(ActivityLog::where('action', 'media.uploaded')
        ->where('subject_id', $mediaAsset->id)
        ->count())->toBe(1);
});

it('logs an entry when an album is created and when a media asset is moved', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/albums", ['name' => 'Ceremony']);

    $album = Album::firstWhere('name', 'Ceremony');

    expect(ActivityLog::where('action', 'media.album_created')
        ->where('subject_id', $album->id)
        ->count())->toBe(1);

    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->patch("/media/{$mediaAsset->uuid}/move", ['album_id' => $album->id]);

    expect(ActivityLog::where('action', 'media.updated')
        ->where('subject_id', $mediaAsset->id)
        ->count())->toBe(1);
});

it('logs an entry naming the task when a media asset is attached to it', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'title' => 'Book DJ']);
    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->patch("/media/{$mediaAsset->uuid}/move", ['task_id' => $task->id]);

    $log = ActivityLog::where('action', 'media.updated')
        ->where('subject_id', $mediaAsset->id)
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->description)->toContain('Book DJ');
});

it('logs an entry naming the expense when a media asset is attached to it', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $expense = Expense::factory()->create(['occasion_id' => $occasion->id, 'amount' => 15000, 'currency' => 'TZS']);
    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->patch("/media/{$mediaAsset->uuid}/move", ['expense_id' => $expense->id]);

    $log = ActivityLog::where('action', 'media.updated')
        ->where('subject_id', $mediaAsset->id)
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->description)->toContain('15000')
        ->and($log->description)->toContain('TZS');
});

it('logs an entry naming the announcement when a media asset is attached to it', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $announcement = Announcement::factory()->create(['occasion_id' => $occasion->id, 'title' => 'Venue Change']);
    $mediaAsset = MediaAsset::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->patch("/media/{$mediaAsset->uuid}/move", ['announcement_id' => $announcement->id]);

    $log = ActivityLog::where('action', 'media.updated')
        ->where('subject_id', $mediaAsset->id)
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->description)->toContain('Venue Change');
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

it('logs an entry when a budget is created and when an expense is recorded', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/budget", [
        'name' => 'Wedding Budget',
        'planned_amount' => 500000,
    ]);

    $budget = Budget::firstWhere('name', 'Wedding Budget');

    $budgetLog = ActivityLog::where('action', 'finance.budget_created')
        ->where('subject_id', $budget->id)
        ->first();

    expect($budgetLog)->not->toBeNull()
        ->and($budgetLog->description)->toContain('TZS');

    $category = BudgetCategory::firstWhere('budget_id', $budget->id);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/expenses", [
        'budget_category_id' => $category->id,
        'amount' => 75000,
        'spent_at' => now()->toDateString(),
    ]);

    $expense = Expense::firstWhere('budget_category_id', $category->id);

    $expenseLog = ActivityLog::where('action', 'finance.expense_recorded')
        ->where('subject_id', $expense->id)
        ->first();

    expect($expenseLog)->not->toBeNull()
        ->and($expenseLog->description)->toContain('TZS');
});
