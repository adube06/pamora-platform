<?php

use App\Domains\Communication\Domain\Models\Announcement;
use App\Domains\Communication\Domain\Models\ReminderRule;
use App\Domains\Finance\Domain\Models\Budget;
use App\Domains\Finance\Domain\Models\BudgetCategory;
use App\Domains\Finance\Domain\Models\BudgetItem;
use App\Domains\Finance\Domain\Models\Contribution;
use App\Domains\Finance\Domain\Models\Expense;
use App\Domains\Finance\Domain\Models\Pledge;
use App\Domains\Marketplace\Application\Services\ApproveVendorService;
use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Domains\Media\Domain\Models\Album;
use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\Role;
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

it('logs an entry when an occasion is edited, archived, and cancelled', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'title' => 'Old Title', 'status' => OccasionStatus::Draft]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)->patch("/occasions/{$occasion->slug}", [
        'title' => 'New Title',
        'type' => $occasion->type->value,
    ]);

    expect(ActivityLog::where('action', 'occasion.updated')->where('occasion_id', $occasion->id)->count())->toBe(1);

    $occasion->update(['status' => OccasionStatus::Completed]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/archive");

    expect(ActivityLog::where('action', 'occasion.archived')->where('occasion_id', $occasion->id)->count())->toBe(1);

    $cancellable = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Draft]);
    OccasionMember::factory()->host()->create(['occasion_id' => $cancellable->id, 'user_id' => $host->id]);

    $this->actingAs($host)->post("/occasions/{$cancellable->slug}/cancel");

    expect(ActivityLog::where('action', 'occasion.cancelled')->where('occasion_id', $cancellable->id)->count())->toBe(1);
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

it('logs an entry when an invitation is declined', function () {
    $invitation = Invitation::factory()->create();

    $this->post("/invitations/{$invitation->token}/decline");

    expect(ActivityLog::where('action', 'people.invitation_declined')
        ->where('subject_id', $invitation->id)
        ->count())->toBe(1);
});

it('logs an entry when a member is removed', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $target = OccasionMember::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->delete("/occasion-members/{$target->uuid}");

    expect(ActivityLog::where('action', 'people.member_removed')
        ->where('subject_id', $target->id)
        ->count())->toBe(1);
});

it('logs an entry when responsibilities are assigned', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $target = OccasionMember::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->patch("/occasion-members/{$target->uuid}/responsibilities", [
        'responsibilities' => ['catering_lead'],
    ]);

    expect(ActivityLog::where('action', 'people.responsibilities_assigned')
        ->where('subject_id', $target->id)
        ->count())->toBe(1);
});

it('logs an entry when a member\'s role is changed', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $target = OccasionMember::factory()->role(Role::Member)->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->patch("/occasion-members/{$target->uuid}/role", ['role' => 'treasurer']);

    expect(ActivityLog::where('action', 'people.role_updated')
        ->where('subject_id', $target->id)
        ->count())->toBe(1);
});

it('logs an entry when ownership is transferred', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $newHostMember = OccasionMember::factory()->role(Role::Treasurer)->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/transfer-ownership", ['member_uuid' => $newHostMember->uuid]);

    expect(ActivityLog::where('action', 'occasion.ownership_transferred')
        ->where('occasion_id', $occasion->id)
        ->count())->toBe(1);
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

it('logs an entry when a task is updated', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id, 'title' => 'Book DJ']);

    $this->actingAs($host)->patch("/tasks/{$task->uuid}", ['title' => 'Book a DJ']);

    expect(ActivityLog::where('action', 'planning.task_updated')->where('subject_id', $task->id)->count())->toBe(1);
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

it('logs an entry when a task dependency is added and when it is removed', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $task = Task::factory()->create(['occasion_id' => $occasion->id]);
    $dependsOnTask = Task::factory()->create(['occasion_id' => $occasion->id]);

    $this->actingAs($host)->post("/tasks/{$task->uuid}/dependencies", ['depends_on_task_id' => $dependsOnTask->id]);

    expect(ActivityLog::where('action', 'planning.task_dependency_added')->where('subject_id', $task->id)->count())->toBe(1);

    $this->actingAs($host)->delete("/tasks/{$task->uuid}/dependencies/{$dependsOnTask->uuid}");

    expect(ActivityLog::where('action', 'planning.task_dependency_removed')->where('subject_id', $task->id)->count())->toBe(1);
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

it('logs an entry when a budget item is added', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);
    $budget = Budget::factory()->create(['occasion_id' => $occasion->id]);
    $category = BudgetCategory::factory()->create(['budget_id' => $budget->id, 'name' => 'Decoration']);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/budget-items", [
        'budget_category_id' => $category->id,
        'name' => 'Balloon arch',
        'estimated_cost' => 800000,
    ]);

    $item = BudgetItem::firstWhere('name', 'Balloon arch');

    $log = ActivityLog::where('action', 'finance.budget_item_added')
        ->where('subject_id', $item->id)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->description)->toContain('Decoration');
});

it('logs an entry when a pledge is recorded and when its status is updated', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/pledges", [
        'pledgor_name' => 'Amina Hassan',
        'amount' => 50000,
        'pledged_at' => now()->toDateString(),
    ]);

    $pledge = Pledge::firstWhere('pledgor_name', 'Amina Hassan');

    expect(ActivityLog::where('action', 'finance.pledge_recorded')
        ->where('subject_id', $pledge->id)
        ->count())->toBe(1);

    $this->actingAs($host)->patch("/occasions/{$occasion->slug}/pledges/{$pledge->uuid}", [
        'status' => 'confirmed',
    ]);

    $log = ActivityLog::where('action', 'finance.pledge_status_updated')
        ->where('subject_id', $pledge->id)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->description)->toContain('Confirmed');
});

it('logs an entry when a vendor applies and when they are approved', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/vendor', [
        'business_name' => 'Amina Photography',
        'categories' => ['photography'],
        'contact_email' => 'hello@aminaphotography.example',
        'contact_phone' => '+255700000000',
    ]);

    $vendor = Vendor::firstWhere('owner_id', $user->id);

    expect(ActivityLog::where('action', 'marketplace.vendor_applied')
        ->where('subject_id', $vendor->id)
        ->count())->toBe(1);

    $admin = User::factory()->create(['is_admin' => true]);
    app(ApproveVendorService::class)->handle($vendor, $admin);

    expect(ActivityLog::where('action', 'marketplace.vendor_approved')
        ->where('subject_id', $vendor->id)
        ->count())->toBe(1);
});

it('logs an entry when a service is published and when it is updated', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id, 'verification_status' => VendorVerificationStatus::Verified]);

    $this->actingAs($owner)->post("/vendor/{$vendor->uuid}/services", [
        'category' => 'photography',
        'name' => 'Wedding Photography',
        'pricing_model' => 'custom',
    ]);

    $service = Service::firstWhere('name', 'Wedding Photography');

    expect(ActivityLog::where('action', 'marketplace.service_published')
        ->where('subject_id', $service->id)
        ->count())->toBe(1);

    $this->actingAs($owner)->patch("/vendor/services/{$service->uuid}", [
        'category' => 'photography',
        'name' => 'Wedding Photography Package',
        'pricing_model' => 'custom',
    ]);

    expect(ActivityLog::where('action', 'marketplace.service_updated')
        ->where('subject_id', $service->id)
        ->count())->toBe(1);
});

it('logs an entry when a quotation is requested and when it is submitted', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $vendorOwner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $vendorOwner->id, 'verification_status' => VendorVerificationStatus::Verified]);
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);

    $this->actingAs($host)->post("/occasions/{$occasion->slug}/quotations", ['service_id' => $service->id]);

    $quotation = Quotation::firstWhere('service_id', $service->id);

    expect(ActivityLog::where('action', 'marketplace.quotation_requested')
        ->where('subject_id', $quotation->id)
        ->count())->toBe(1);

    $this->actingAs($vendorOwner)->patch("/quotations/{$quotation->uuid}/submit", ['quoted_price' => 100000]);

    expect(ActivityLog::where('action', 'marketplace.quotation_submitted')
        ->where('subject_id', $quotation->id)
        ->count())->toBe(1);
});
