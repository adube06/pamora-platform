<?php

namespace App\Domains\Shared\Infrastructure\ActivityLog;

use App\Domains\Communication\Domain\Events\AnnouncementPublished;
use App\Domains\Communication\Domain\Events\ReminderRuleScheduled;
use App\Domains\Communication\Domain\Events\ReminderTriggered;
use App\Domains\Finance\Domain\Events\BudgetCreated;
use App\Domains\Finance\Domain\Events\ContributionReceived;
use App\Domains\Finance\Domain\Events\ExpenseRecorded;
use App\Domains\Identity\Domain\Events\UserRegistered;
use App\Domains\Identity\Domain\Events\UserSignedIn;
use App\Domains\Media\Domain\Events\AlbumCreated;
use App\Domains\Media\Domain\Events\MediaUpdated;
use App\Domains\Media\Domain\Events\MediaUploaded;
use App\Domains\Media\Domain\Models\Album;
use App\Domains\Occasion\Domain\Events\OccasionCreated;
use App\Domains\People\Domain\Events\MemberInvited;
use App\Domains\People\Domain\Events\MemberJoined;
use App\Domains\People\Domain\Events\RsvpReopened;
use App\Domains\People\Domain\Events\RsvpSubmitted;
use App\Domains\Planning\Domain\Events\ChecklistCreated;
use App\Domains\Planning\Domain\Events\MilestoneCompleted;
use App\Domains\Planning\Domain\Events\TaskAssigned;
use App\Domains\Planning\Domain\Events\TaskCompleted;
use App\Domains\Planning\Domain\Events\TaskCreated;
use App\Domains\Planning\Domain\Events\TaskReopened;
use App\Domains\Planning\Domain\Events\TimelineEventScheduled;
use Illuminate\Events\Dispatcher;

/**
 * The single place BR-036 ("every significant action must create an
 * Activity Log entry") is satisfied. No domain calls an audit service
 * directly (ADR-006) — this subscriber listens to every domain event
 * that matters and writes one immutable row per event.
 */
class AuditLogSubscriber
{
    public function handleUserRegistered(UserRegistered $event): void
    {
        ActivityLog::create([
            'occasion_id' => null,
            'user_id' => $event->user->id,
            'subject_type' => 'User',
            'subject_id' => $event->user->id,
            'action' => 'identity.user_registered',
            'description' => "{$event->user->name} registered an account.",
        ]);
    }

    public function handleUserSignedIn(UserSignedIn $event): void
    {
        ActivityLog::create([
            'occasion_id' => null,
            'user_id' => $event->user->id,
            'subject_type' => 'User',
            'subject_id' => $event->user->id,
            'action' => 'identity.user_signed_in',
            'description' => "{$event->user->name} signed in.",
        ]);
    }

    public function handleOccasionCreated(OccasionCreated $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->occasion->id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Occasion',
            'subject_id' => $event->occasion->id,
            'action' => 'occasion.created',
            'description' => "{$event->actor->name} created \"{$event->occasion->title}\".",
        ]);
    }

    public function handleMemberInvited(MemberInvited $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->invitation->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Invitation',
            'subject_id' => $event->invitation->id,
            'action' => 'people.member_invited',
            'description' => "{$event->actor->name} invited {$event->invitation->email}.",
        ]);
    }

    public function handleMemberJoined(MemberJoined $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->member->occasion_id,
            'user_id' => $event->member->user_id,
            'subject_type' => 'OccasionMember',
            'subject_id' => $event->member->id,
            'action' => 'people.member_joined',
            'description' => "{$event->member->user->name} joined the Occasion.",
        ]);
    }

    public function handleRsvpSubmitted(RsvpSubmitted $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->member->occasion_id,
            'user_id' => $event->member->user_id,
            'subject_type' => 'OccasionMember',
            'subject_id' => $event->member->id,
            'action' => 'people.rsvp_submitted',
            'description' => "{$event->member->user->name} responded {$event->member->rsvp_status->label()} to the Occasion.",
        ]);
    }

    public function handleRsvpReopened(RsvpReopened $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->member->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'OccasionMember',
            'subject_id' => $event->member->id,
            'action' => 'people.rsvp_reopened',
            'description' => "{$event->actor->name} reopened RSVP for {$event->member->user->name}.",
        ]);
    }

    public function handleTaskCreated(TaskCreated $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->task->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Task',
            'subject_id' => $event->task->id,
            'action' => 'planning.task_created',
            'description' => "{$event->actor->name} created task \"{$event->task->title}\".",
        ]);
    }

    public function handleTaskAssigned(TaskAssigned $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->task->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Task',
            'subject_id' => $event->task->id,
            'action' => 'planning.task_assigned',
            'description' => "{$event->actor->name} assigned task \"{$event->task->title}\".",
            'metadata' => ['assignee_id' => $event->task->assignee_id],
        ]);
    }

    public function handleTaskCompleted(TaskCompleted $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->task->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Task',
            'subject_id' => $event->task->id,
            'action' => 'planning.task_completed',
            'description' => "{$event->actor->name} completed task \"{$event->task->title}\".",
        ]);
    }

    public function handleTaskReopened(TaskReopened $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->task->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Task',
            'subject_id' => $event->task->id,
            'action' => 'planning.task_reopened',
            'description' => "{$event->actor->name} reopened task \"{$event->task->title}\".",
        ]);
    }

    public function handleChecklistCreated(ChecklistCreated $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->checklist->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Checklist',
            'subject_id' => $event->checklist->id,
            'action' => 'planning.checklist_created',
            'description' => "{$event->actor->name} created checklist \"{$event->checklist->name}\".",
        ]);
    }

    public function handleMilestoneCompleted(MilestoneCompleted $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->milestone->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Milestone',
            'subject_id' => $event->milestone->id,
            'action' => 'planning.milestone_completed',
            'description' => "{$event->actor->name} completed the task that achieved milestone \"{$event->milestone->name}\".",
        ]);
    }

    public function handleTimelineEventScheduled(TimelineEventScheduled $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->timelineEvent->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'TimelineEvent',
            'subject_id' => $event->timelineEvent->id,
            'action' => 'planning.timeline_event_scheduled',
            'description' => "{$event->actor->name} scheduled \"{$event->timelineEvent->name}\" on the timeline.",
        ]);
    }

    public function handleAnnouncementPublished(AnnouncementPublished $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->announcement->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Announcement',
            'subject_id' => $event->announcement->id,
            'action' => 'communication.announcement_published',
            'description' => "{$event->actor->name} published \"{$event->announcement->title}\".",
        ]);
    }

    public function handleReminderRuleScheduled(ReminderRuleScheduled $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->reminderRule->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'ReminderRule',
            'subject_id' => $event->reminderRule->id,
            'action' => 'communication.reminder_scheduled',
            'description' => "{$event->actor->name} scheduled a reminder for \"{$event->reminderRule->timelineEvent->name}\".",
        ]);
    }

    public function handleReminderTriggered(ReminderTriggered $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->reminderRule->occasion_id,
            'user_id' => null,
            'subject_type' => 'ReminderRule',
            'subject_id' => $event->reminderRule->id,
            'action' => 'communication.reminder_triggered',
            'description' => "Reminder triggered for \"{$event->reminderRule->timelineEvent->name}\".",
        ]);
    }

    public function handleMediaUploaded(MediaUploaded $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->mediaAsset->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'MediaAsset',
            'subject_id' => $event->mediaAsset->id,
            'action' => 'media.uploaded',
            'description' => "{$event->actor->name} uploaded \"{$event->mediaAsset->file_name}\".",
        ]);
    }

    public function handleAlbumCreated(AlbumCreated $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->album->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Album',
            'subject_id' => $event->album->id,
            'action' => 'media.album_created',
            'description' => "{$event->actor->name} created album \"{$event->album->name}\".",
        ]);
    }

    public function handleMediaUpdated(MediaUpdated $event): void
    {
        $destination = $event->mediaAsset->attachable instanceof Album
            ? "album \"{$event->mediaAsset->attachable->name}\""
            : 'the Occasion gallery';

        ActivityLog::create([
            'occasion_id' => $event->mediaAsset->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'MediaAsset',
            'subject_id' => $event->mediaAsset->id,
            'action' => 'media.updated',
            'description' => "{$event->actor->name} moved \"{$event->mediaAsset->file_name}\" to {$destination}.",
        ]);
    }

    public function handleContributionReceived(ContributionReceived $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->contribution->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Contribution',
            'subject_id' => $event->contribution->id,
            'action' => 'finance.contribution_received',
            'description' => "{$event->actor->name} recorded a contribution of {$event->contribution->amount} {$event->contribution->currency} from {$event->contribution->contributor_name}.",
            'metadata' => ['method' => $event->contribution->method->value],
        ]);
    }

    public function handleBudgetCreated(BudgetCreated $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->budget->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Budget',
            'subject_id' => $event->budget->id,
            'action' => 'finance.budget_created',
            'description' => "{$event->actor->name} created a Budget of {$event->budget->planned_amount} {$event->budget->currency}.",
        ]);
    }

    public function handleExpenseRecorded(ExpenseRecorded $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->expense->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Expense',
            'subject_id' => $event->expense->id,
            'action' => 'finance.expense_recorded',
            'description' => "{$event->actor->name} recorded an expense of {$event->expense->amount} {$event->expense->currency}.",
            'metadata' => ['budget_category_id' => $event->expense->budget_category_id],
        ]);
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(UserRegistered::class, [self::class, 'handleUserRegistered']);
        $events->listen(UserSignedIn::class, [self::class, 'handleUserSignedIn']);
        $events->listen(OccasionCreated::class, [self::class, 'handleOccasionCreated']);
        $events->listen(MemberInvited::class, [self::class, 'handleMemberInvited']);
        $events->listen(MemberJoined::class, [self::class, 'handleMemberJoined']);
        $events->listen(RsvpSubmitted::class, [self::class, 'handleRsvpSubmitted']);
        $events->listen(RsvpReopened::class, [self::class, 'handleRsvpReopened']);
        $events->listen(TaskCreated::class, [self::class, 'handleTaskCreated']);
        $events->listen(TaskAssigned::class, [self::class, 'handleTaskAssigned']);
        $events->listen(TaskCompleted::class, [self::class, 'handleTaskCompleted']);
        $events->listen(TaskReopened::class, [self::class, 'handleTaskReopened']);
        $events->listen(ChecklistCreated::class, [self::class, 'handleChecklistCreated']);
        $events->listen(MilestoneCompleted::class, [self::class, 'handleMilestoneCompleted']);
        $events->listen(TimelineEventScheduled::class, [self::class, 'handleTimelineEventScheduled']);
        $events->listen(AnnouncementPublished::class, [self::class, 'handleAnnouncementPublished']);
        $events->listen(ReminderRuleScheduled::class, [self::class, 'handleReminderRuleScheduled']);
        $events->listen(ReminderTriggered::class, [self::class, 'handleReminderTriggered']);
        $events->listen(MediaUploaded::class, [self::class, 'handleMediaUploaded']);
        $events->listen(AlbumCreated::class, [self::class, 'handleAlbumCreated']);
        $events->listen(MediaUpdated::class, [self::class, 'handleMediaUpdated']);
        $events->listen(ContributionReceived::class, [self::class, 'handleContributionReceived']);
        $events->listen(BudgetCreated::class, [self::class, 'handleBudgetCreated']);
        $events->listen(ExpenseRecorded::class, [self::class, 'handleExpenseRecorded']);
    }
}
