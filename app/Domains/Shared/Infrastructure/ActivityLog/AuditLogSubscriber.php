<?php

namespace App\Domains\Shared\Infrastructure\ActivityLog;

use App\Domains\Communication\Domain\Events\AnnouncementPublished;
use App\Domains\Communication\Domain\Events\PreferenceUpdated;
use App\Domains\Communication\Domain\Events\ReminderRuleScheduled;
use App\Domains\Communication\Domain\Events\ReminderTriggered;
use App\Domains\Communication\Domain\Models\Announcement;
use App\Domains\Finance\Domain\Events\BudgetCreated;
use App\Domains\Finance\Domain\Events\BudgetItemAdded;
use App\Domains\Finance\Domain\Events\ContributionReceived;
use App\Domains\Finance\Domain\Events\ExpenseRecorded;
use App\Domains\Finance\Domain\Events\PledgeRecorded;
use App\Domains\Finance\Domain\Events\PledgeStatusUpdated;
use App\Domains\Finance\Domain\Models\Expense;
use App\Domains\Identity\Domain\Events\SessionRevoked;
use App\Domains\Identity\Domain\Events\UserRegistered;
use App\Domains\Identity\Domain\Events\UserSignedIn;
use App\Domains\Marketplace\Domain\Events\BookingCompleted;
use App\Domains\Marketplace\Domain\Events\BookingConfirmed;
use App\Domains\Marketplace\Domain\Events\QuotationAccepted;
use App\Domains\Marketplace\Domain\Events\QuotationRejected;
use App\Domains\Marketplace\Domain\Events\QuotationRequested;
use App\Domains\Marketplace\Domain\Events\QuotationSubmitted;
use App\Domains\Marketplace\Domain\Events\ServicePublished;
use App\Domains\Marketplace\Domain\Events\ServiceUpdated;
use App\Domains\Marketplace\Domain\Events\VendorApplied;
use App\Domains\Marketplace\Domain\Events\VendorApproved;
use App\Domains\Marketplace\Domain\Events\VendorRejected;
use App\Domains\Media\Domain\Events\AlbumCreated;
use App\Domains\Media\Domain\Events\MediaUpdated;
use App\Domains\Media\Domain\Events\MediaUploaded;
use App\Domains\Media\Domain\Models\Album;
use App\Domains\Occasion\Domain\Events\OccasionArchived;
use App\Domains\Occasion\Domain\Events\OccasionCancelled;
use App\Domains\Occasion\Domain\Events\OccasionCreated;
use App\Domains\Occasion\Domain\Events\OccasionOwnershipTransferred;
use App\Domains\Occasion\Domain\Events\OccasionUpdated;
use App\Domains\People\Domain\Enums\Responsibility;
use App\Domains\People\Domain\Events\InvitationDeclined;
use App\Domains\People\Domain\Events\MemberInvited;
use App\Domains\People\Domain\Events\MemberJoined;
use App\Domains\People\Domain\Events\MemberRemoved;
use App\Domains\People\Domain\Events\MemberRoleUpdated;
use App\Domains\People\Domain\Events\ResponsibilityAssigned;
use App\Domains\People\Domain\Events\RsvpReopened;
use App\Domains\People\Domain\Events\RsvpSubmitted;
use App\Domains\Planning\Domain\Events\ChecklistCreated;
use App\Domains\Planning\Domain\Events\MilestoneCompleted;
use App\Domains\Planning\Domain\Events\TaskAssigned;
use App\Domains\Planning\Domain\Events\TaskCompleted;
use App\Domains\Planning\Domain\Events\TaskCreated;
use App\Domains\Planning\Domain\Events\TaskDependencyAdded;
use App\Domains\Planning\Domain\Events\TaskDependencyRemoved;
use App\Domains\Planning\Domain\Events\TaskReopened;
use App\Domains\Planning\Domain\Events\TaskUpdated;
use App\Domains\Planning\Domain\Events\TimelineEventScheduled;
use App\Domains\Planning\Domain\Models\Task;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
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

    public function handleSessionRevoked(SessionRevoked $event): void
    {
        ActivityLog::create([
            'occasion_id' => null,
            'user_id' => $event->actor->id,
            'subject_type' => 'User',
            'subject_id' => $event->user->id,
            'action' => 'identity.session_revoked',
            'description' => "{$event->actor->name} revoked a session.",
        ]);
    }

    /**
     * Listens to Laravel's own framework event (fired by
     * EmailVerificationRequest::fulfill()) rather than a duplicate custom
     * Identity event — the one deliberate exception to "every audited
     * action has its own Domain event," since the framework's own signal
     * already carries exactly what BR-036 needs here.
     */
    public function handleVerified(Verified $event): void
    {
        ActivityLog::create([
            'occasion_id' => null,
            'user_id' => $event->user->id,
            'subject_type' => 'User',
            'subject_id' => $event->user->id,
            'action' => 'identity.email_verified',
            'description' => "{$event->user->name} verified their email address.",
        ]);
    }

    /**
     * Same reasoning as handleVerified() — Password::reset()'s callback
     * already fires this framework event once the password is updated.
     */
    public function handlePasswordReset(PasswordReset $event): void
    {
        ActivityLog::create([
            'occasion_id' => null,
            'user_id' => $event->user->id,
            'subject_type' => 'User',
            'subject_id' => $event->user->id,
            'action' => 'identity.password_reset',
            'description' => "{$event->user->name} reset their password.",
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

    public function handleOccasionUpdated(OccasionUpdated $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->occasion->id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Occasion',
            'subject_id' => $event->occasion->id,
            'action' => 'occasion.updated',
            'description' => "{$event->actor->name} updated \"{$event->occasion->title}\".",
        ]);
    }

    public function handleOccasionArchived(OccasionArchived $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->occasion->id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Occasion',
            'subject_id' => $event->occasion->id,
            'action' => 'occasion.archived',
            'description' => "{$event->actor->name} archived \"{$event->occasion->title}\".",
        ]);
    }

    public function handleOccasionCancelled(OccasionCancelled $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->occasion->id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Occasion',
            'subject_id' => $event->occasion->id,
            'action' => 'occasion.cancelled',
            'description' => "{$event->actor->name} cancelled \"{$event->occasion->title}\".",
        ]);
    }

    public function handleOccasionOwnershipTransferred(OccasionOwnershipTransferred $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->occasion->id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Occasion',
            'subject_id' => $event->occasion->id,
            'action' => 'occasion.ownership_transferred',
            'description' => "{$event->actor->name} transferred ownership of \"{$event->occasion->title}\" from {$event->previousHost->name} to {$event->newHost->name}.",
        ]);
    }

    public function handleInvitationDeclined(InvitationDeclined $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->invitation->occasion_id,
            'user_id' => null,
            'subject_type' => 'Invitation',
            'subject_id' => $event->invitation->id,
            'action' => 'people.invitation_declined',
            'description' => "{$event->invitation->email} declined the invitation.",
        ]);
    }

    public function handleMemberRemoved(MemberRemoved $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->member->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'OccasionMember',
            'subject_id' => $event->member->id,
            'action' => 'people.member_removed',
            'description' => "{$event->actor->name} removed {$event->member->user->name} from the Occasion.",
        ]);
    }

    public function handleResponsibilityAssigned(ResponsibilityAssigned $event): void
    {
        $labels = collect($event->member->responsibilities ?? [])
            ->map(fn (string $value) => Responsibility::from($value)->label())
            ->implode(', ');

        ActivityLog::create([
            'occasion_id' => $event->member->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'OccasionMember',
            'subject_id' => $event->member->id,
            'action' => 'people.responsibilities_assigned',
            'description' => $labels !== ''
                ? "{$event->actor->name} set {$event->member->user->name}'s responsibilities to: {$labels}."
                : "{$event->actor->name} cleared {$event->member->user->name}'s responsibilities.",
        ]);
    }

    public function handleMemberRoleUpdated(MemberRoleUpdated $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->member->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'OccasionMember',
            'subject_id' => $event->member->id,
            'action' => 'people.role_updated',
            'description' => "{$event->actor->name} changed {$event->member->user->name}'s role from {$event->previousRole->label()} to {$event->member->role->label()}.",
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

    public function handleTaskUpdated(TaskUpdated $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->task->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Task',
            'subject_id' => $event->task->id,
            'action' => 'planning.task_updated',
            'description' => "{$event->actor->name} updated task \"{$event->task->title}\".",
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

    public function handleTaskDependencyAdded(TaskDependencyAdded $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->task->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Task',
            'subject_id' => $event->task->id,
            'action' => 'planning.task_dependency_added',
            'description' => "{$event->actor->name} made \"{$event->task->title}\" depend on \"{$event->dependsOnTask->title}\".",
        ]);
    }

    public function handleTaskDependencyRemoved(TaskDependencyRemoved $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->task->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Task',
            'subject_id' => $event->task->id,
            'action' => 'planning.task_dependency_removed',
            'description' => "{$event->actor->name} removed \"{$event->task->title}\"'s dependency on \"{$event->dependsOnTask->title}\".",
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

    public function handlePreferenceUpdated(PreferenceUpdated $event): void
    {
        ActivityLog::create([
            'occasion_id' => null,
            'user_id' => $event->actor->id,
            'subject_type' => 'User',
            'subject_id' => $event->user->id,
            'action' => 'communication.preferences_updated',
            'description' => "{$event->user->name} updated their notification preferences.",
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
        $destination = match (true) {
            $event->mediaAsset->attachable instanceof Album => "album \"{$event->mediaAsset->attachable->name}\"",
            $event->mediaAsset->attachable instanceof Task => "task \"{$event->mediaAsset->attachable->title}\"",
            $event->mediaAsset->attachable instanceof Expense => "the receipt for {$event->mediaAsset->attachable->amount} {$event->mediaAsset->attachable->currency} expense",
            $event->mediaAsset->attachable instanceof Announcement => "announcement \"{$event->mediaAsset->attachable->title}\"",
            default => 'the Occasion gallery',
        };

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

    public function handleBudgetItemAdded(BudgetItemAdded $event): void
    {
        $category = $event->budgetItem->category;

        ActivityLog::create([
            'occasion_id' => $category->budget->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'BudgetItem',
            'subject_id' => $event->budgetItem->id,
            'action' => 'finance.budget_item_added',
            'description' => "{$event->actor->name} added budget item \"{$event->budgetItem->name}\" ({$event->budgetItem->estimated_cost} {$event->budgetItem->currency}) to {$category->name}.",
        ]);
    }

    public function handlePledgeRecorded(PledgeRecorded $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->pledge->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Pledge',
            'subject_id' => $event->pledge->id,
            'action' => 'finance.pledge_recorded',
            'description' => "{$event->actor->name} recorded a pledge of {$event->pledge->amount} {$event->pledge->currency} from {$event->pledge->pledgor_name}.",
        ]);
    }

    public function handlePledgeStatusUpdated(PledgeStatusUpdated $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->pledge->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Pledge',
            'subject_id' => $event->pledge->id,
            'action' => 'finance.pledge_status_updated',
            'description' => "{$event->actor->name} marked {$event->pledge->pledgor_name}'s pledge as {$event->pledge->status->label()}.",
        ]);
    }

    public function handleVendorApplied(VendorApplied $event): void
    {
        ActivityLog::create([
            'occasion_id' => null,
            'user_id' => $event->actor->id,
            'subject_type' => 'Vendor',
            'subject_id' => $event->vendor->id,
            'action' => 'marketplace.vendor_applied',
            'description' => "{$event->actor->name} applied to become a Vendor as \"{$event->vendor->business_name}\".",
        ]);
    }

    public function handleVendorApproved(VendorApproved $event): void
    {
        ActivityLog::create([
            'occasion_id' => null,
            'user_id' => $event->actor->id,
            'subject_type' => 'Vendor',
            'subject_id' => $event->vendor->id,
            'action' => 'marketplace.vendor_approved',
            'description' => "{$event->actor->name} approved Vendor \"{$event->vendor->business_name}\".",
        ]);
    }

    public function handleVendorRejected(VendorRejected $event): void
    {
        ActivityLog::create([
            'occasion_id' => null,
            'user_id' => $event->actor->id,
            'subject_type' => 'Vendor',
            'subject_id' => $event->vendor->id,
            'action' => 'marketplace.vendor_rejected',
            'description' => "{$event->actor->name} rejected Vendor \"{$event->vendor->business_name}\".",
        ]);
    }

    public function handleServicePublished(ServicePublished $event): void
    {
        ActivityLog::create([
            'occasion_id' => null,
            'user_id' => $event->actor->id,
            'subject_type' => 'Service',
            'subject_id' => $event->service->id,
            'action' => 'marketplace.service_published',
            'description' => "{$event->actor->name} published Service \"{$event->service->name}\".",
        ]);
    }

    public function handleServiceUpdated(ServiceUpdated $event): void
    {
        ActivityLog::create([
            'occasion_id' => null,
            'user_id' => $event->actor->id,
            'subject_type' => 'Service',
            'subject_id' => $event->service->id,
            'action' => 'marketplace.service_updated',
            'description' => "{$event->actor->name} updated Service \"{$event->service->name}\".",
        ]);
    }

    public function handleQuotationRequested(QuotationRequested $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->quotation->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Quotation',
            'subject_id' => $event->quotation->id,
            'action' => 'marketplace.quotation_requested',
            'description' => "{$event->actor->name} requested a quotation for \"{$event->quotation->service->name}\".",
        ]);
    }

    public function handleQuotationSubmitted(QuotationSubmitted $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->quotation->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Quotation',
            'subject_id' => $event->quotation->id,
            'action' => 'marketplace.quotation_submitted',
            'description' => "{$event->actor->name} submitted a quotation for \"{$event->quotation->service->name}\".",
        ]);
    }

    public function handleQuotationAccepted(QuotationAccepted $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->quotation->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Quotation',
            'subject_id' => $event->quotation->id,
            'action' => 'marketplace.quotation_accepted',
            'description' => "{$event->actor->name} accepted the quotation for \"{$event->quotation->service->name}\".",
        ]);
    }

    public function handleQuotationRejected(QuotationRejected $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->quotation->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Quotation',
            'subject_id' => $event->quotation->id,
            'action' => 'marketplace.quotation_rejected',
            'description' => "{$event->actor->name} rejected the quotation for \"{$event->quotation->service->name}\".",
        ]);
    }

    public function handleBookingConfirmed(BookingConfirmed $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->booking->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Booking',
            'subject_id' => $event->booking->id,
            'action' => 'marketplace.booking_confirmed',
            'description' => "{$event->actor->name} confirmed a Booking for \"{$event->booking->service->name}\".",
        ]);
    }

    public function handleBookingCompleted(BookingCompleted $event): void
    {
        ActivityLog::create([
            'occasion_id' => $event->booking->occasion_id,
            'user_id' => $event->actor->id,
            'subject_type' => 'Booking',
            'subject_id' => $event->booking->id,
            'action' => 'marketplace.booking_completed',
            'description' => "{$event->actor->name} marked the Booking for \"{$event->booking->service->name}\" complete.",
        ]);
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(VendorApplied::class, [self::class, 'handleVendorApplied']);
        $events->listen(VendorApproved::class, [self::class, 'handleVendorApproved']);
        $events->listen(VendorRejected::class, [self::class, 'handleVendorRejected']);
        $events->listen(ServicePublished::class, [self::class, 'handleServicePublished']);
        $events->listen(ServiceUpdated::class, [self::class, 'handleServiceUpdated']);
        $events->listen(QuotationRequested::class, [self::class, 'handleQuotationRequested']);
        $events->listen(QuotationSubmitted::class, [self::class, 'handleQuotationSubmitted']);
        $events->listen(QuotationAccepted::class, [self::class, 'handleQuotationAccepted']);
        $events->listen(QuotationRejected::class, [self::class, 'handleQuotationRejected']);
        $events->listen(BookingConfirmed::class, [self::class, 'handleBookingConfirmed']);
        $events->listen(BookingCompleted::class, [self::class, 'handleBookingCompleted']);
        $events->listen(UserRegistered::class, [self::class, 'handleUserRegistered']);
        $events->listen(UserSignedIn::class, [self::class, 'handleUserSignedIn']);
        $events->listen(SessionRevoked::class, [self::class, 'handleSessionRevoked']);
        $events->listen(Verified::class, [self::class, 'handleVerified']);
        $events->listen(PasswordReset::class, [self::class, 'handlePasswordReset']);
        $events->listen(OccasionCreated::class, [self::class, 'handleOccasionCreated']);
        $events->listen(OccasionUpdated::class, [self::class, 'handleOccasionUpdated']);
        $events->listen(OccasionArchived::class, [self::class, 'handleOccasionArchived']);
        $events->listen(OccasionCancelled::class, [self::class, 'handleOccasionCancelled']);
        $events->listen(OccasionOwnershipTransferred::class, [self::class, 'handleOccasionOwnershipTransferred']);
        $events->listen(InvitationDeclined::class, [self::class, 'handleInvitationDeclined']);
        $events->listen(MemberRemoved::class, [self::class, 'handleMemberRemoved']);
        $events->listen(ResponsibilityAssigned::class, [self::class, 'handleResponsibilityAssigned']);
        $events->listen(MemberRoleUpdated::class, [self::class, 'handleMemberRoleUpdated']);
        $events->listen(MemberInvited::class, [self::class, 'handleMemberInvited']);
        $events->listen(MemberJoined::class, [self::class, 'handleMemberJoined']);
        $events->listen(RsvpSubmitted::class, [self::class, 'handleRsvpSubmitted']);
        $events->listen(RsvpReopened::class, [self::class, 'handleRsvpReopened']);
        $events->listen(TaskCreated::class, [self::class, 'handleTaskCreated']);
        $events->listen(TaskUpdated::class, [self::class, 'handleTaskUpdated']);
        $events->listen(TaskAssigned::class, [self::class, 'handleTaskAssigned']);
        $events->listen(TaskCompleted::class, [self::class, 'handleTaskCompleted']);
        $events->listen(TaskReopened::class, [self::class, 'handleTaskReopened']);
        $events->listen(TaskDependencyAdded::class, [self::class, 'handleTaskDependencyAdded']);
        $events->listen(TaskDependencyRemoved::class, [self::class, 'handleTaskDependencyRemoved']);
        $events->listen(ChecklistCreated::class, [self::class, 'handleChecklistCreated']);
        $events->listen(MilestoneCompleted::class, [self::class, 'handleMilestoneCompleted']);
        $events->listen(TimelineEventScheduled::class, [self::class, 'handleTimelineEventScheduled']);
        $events->listen(AnnouncementPublished::class, [self::class, 'handleAnnouncementPublished']);
        $events->listen(PreferenceUpdated::class, [self::class, 'handlePreferenceUpdated']);
        $events->listen(ReminderRuleScheduled::class, [self::class, 'handleReminderRuleScheduled']);
        $events->listen(ReminderTriggered::class, [self::class, 'handleReminderTriggered']);
        $events->listen(MediaUploaded::class, [self::class, 'handleMediaUploaded']);
        $events->listen(AlbumCreated::class, [self::class, 'handleAlbumCreated']);
        $events->listen(MediaUpdated::class, [self::class, 'handleMediaUpdated']);
        $events->listen(ContributionReceived::class, [self::class, 'handleContributionReceived']);
        $events->listen(BudgetCreated::class, [self::class, 'handleBudgetCreated']);
        $events->listen(ExpenseRecorded::class, [self::class, 'handleExpenseRecorded']);
        $events->listen(BudgetItemAdded::class, [self::class, 'handleBudgetItemAdded']);
        $events->listen(PledgeRecorded::class, [self::class, 'handlePledgeRecorded']);
        $events->listen(PledgeStatusUpdated::class, [self::class, 'handlePledgeStatusUpdated']);
    }
}
