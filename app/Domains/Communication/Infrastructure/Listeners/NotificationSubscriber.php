<?php

namespace App\Domains\Communication\Infrastructure\Listeners;

use App\Domains\Communication\Domain\Events\ReminderTriggered;
use App\Domains\Communication\Domain\Models\Notification;
use App\Domains\Finance\Domain\Events\ContributionReceived;
use App\Domains\Marketplace\Domain\Events\BookingCompleted;
use App\Domains\Marketplace\Domain\Events\BookingConfirmed;
use App\Domains\Marketplace\Domain\Events\QuotationAccepted;
use App\Domains\Marketplace\Domain\Events\QuotationRejected;
use App\Domains\Marketplace\Domain\Events\QuotationSubmitted;
use App\Domains\Marketplace\Domain\Events\ReviewPublished;
use App\Domains\People\Domain\Events\MemberJoined;
use App\Domains\Planning\Domain\Events\TaskAssigned;
use App\Domains\Planning\Domain\Events\TaskCompleted;
use App\Models\User;

/**
 * FR-002 — generates in-app Notifications from Domain Events (ADR-006).
 * A distinct concern from AuditLogSubscriber (recipient-facing, not
 * compliance-facing) even though both react to the same events.
 */
class NotificationSubscriber
{
    public function handleTaskAssigned(TaskAssigned $event): void
    {
        $assigneeUserId = $event->task->assignee->user_id;

        // Don't notify a member of their own action.
        if ($assigneeUserId === $event->actor->id) {
            return;
        }

        if (! User::find($assigneeUserId)?->wantsNotification('task_assigned')) {
            return;
        }

        Notification::create([
            'user_id' => $assigneeUserId,
            'occasion_id' => $event->task->occasion_id,
            'subject_type' => 'Task',
            'subject_id' => $event->task->id,
            'type' => 'task_assigned',
            'title' => 'New task assigned to you',
            'body' => "{$event->actor->name} assigned you to \"{$event->task->title}\".",
        ]);
    }

    public function handleTaskCompleted(TaskCompleted $event): void
    {
        // Don't notify a member of their own action.
        if ($event->task->created_by === $event->actor->id) {
            return;
        }

        if (! User::find($event->task->created_by)?->wantsNotification('task_completed')) {
            return;
        }

        Notification::create([
            'user_id' => $event->task->created_by,
            'occasion_id' => $event->task->occasion_id,
            'subject_type' => 'Task',
            'subject_id' => $event->task->id,
            'type' => 'task_completed',
            'title' => 'Task completed',
            'body' => "{$event->actor->name} completed \"{$event->task->title}\".",
        ]);
    }

    public function handleContributionReceived(ContributionReceived $event): void
    {
        $occasion = $event->contribution->occasion;

        // Don't notify a member of their own action.
        if ($occasion->host_id === $event->actor->id) {
            return;
        }

        if (! User::find($occasion->host_id)?->wantsNotification('contribution_received')) {
            return;
        }

        Notification::create([
            'user_id' => $occasion->host_id,
            'occasion_id' => $occasion->id,
            'subject_type' => 'Contribution',
            'subject_id' => $event->contribution->id,
            'type' => 'contribution_received',
            'title' => 'New contribution received',
            'body' => "{$event->actor->name} recorded a contribution of {$event->contribution->amount} {$event->contribution->currency} from {$event->contribution->contributor_name}.",
        ]);
    }

    public function handleMemberJoined(MemberJoined $event): void
    {
        $occasion = $event->member->occasion;

        // MemberJoined also fires for the Host's own initial membership
        // (CreateHostMembershipService) — skip so the Host isn't notified
        // about themselves on every Occasion creation.
        if ($occasion->host_id === $event->member->user_id) {
            return;
        }

        if (! User::find($occasion->host_id)?->wantsNotification('member_joined')) {
            return;
        }

        Notification::create([
            'user_id' => $occasion->host_id,
            'occasion_id' => $occasion->id,
            'subject_type' => 'OccasionMember',
            'subject_id' => $event->member->id,
            'type' => 'member_joined',
            'title' => 'New member joined',
            'body' => "{$event->member->user->name} joined the Occasion as {$event->member->role->label()}.",
        ]);
    }

    public function handleReminderTriggered(ReminderTriggered $event): void
    {
        $timelineEvent = $event->reminderRule->timelineEvent;

        if (! User::find($event->reminderRule->created_by)?->wantsNotification('reminder_triggered')) {
            return;
        }

        Notification::create([
            'user_id' => $event->reminderRule->created_by,
            'occasion_id' => $event->reminderRule->occasion_id,
            'subject_type' => 'TimelineEvent',
            'subject_id' => $timelineEvent->id,
            'type' => 'reminder_triggered',
            'title' => 'Reminder',
            'body' => "\"{$timelineEvent->name}\" is coming up.",
        ]);
    }

    public function handleQuotationSubmitted(QuotationSubmitted $event): void
    {
        $hostUserId = $event->quotation->requested_by;

        // Don't notify a member of their own action.
        if ($hostUserId === $event->actor->id) {
            return;
        }

        if (! User::find($hostUserId)?->wantsNotification('quotation_submitted')) {
            return;
        }

        Notification::create([
            'user_id' => $hostUserId,
            'occasion_id' => $event->quotation->occasion_id,
            'subject_type' => 'Quotation',
            'subject_id' => $event->quotation->id,
            'type' => 'quotation_submitted',
            'title' => 'Quotation submitted',
            'body' => "A Vendor submitted a quotation for \"{$event->quotation->service->name}\".",
        ]);
    }

    public function handleQuotationAccepted(QuotationAccepted $event): void
    {
        $vendorOwnerId = $event->quotation->service->vendor->owner_id;

        // Don't notify a member of their own action.
        if ($vendorOwnerId === $event->actor->id) {
            return;
        }

        if (! User::find($vendorOwnerId)?->wantsNotification('quotation_accepted')) {
            return;
        }

        Notification::create([
            'user_id' => $vendorOwnerId,
            'occasion_id' => $event->quotation->occasion_id,
            'subject_type' => 'Quotation',
            'subject_id' => $event->quotation->id,
            'type' => 'quotation_accepted',
            'title' => 'Quotation accepted',
            'body' => "Your quotation for \"{$event->quotation->service->name}\" was accepted.",
        ]);
    }

    public function handleQuotationRejected(QuotationRejected $event): void
    {
        $vendorOwnerId = $event->quotation->service->vendor->owner_id;

        // Don't notify a member of their own action.
        if ($vendorOwnerId === $event->actor->id) {
            return;
        }

        if (! User::find($vendorOwnerId)?->wantsNotification('quotation_rejected')) {
            return;
        }

        Notification::create([
            'user_id' => $vendorOwnerId,
            'occasion_id' => $event->quotation->occasion_id,
            'subject_type' => 'Quotation',
            'subject_id' => $event->quotation->id,
            'type' => 'quotation_rejected',
            'title' => 'Quotation rejected',
            'body' => "Your quotation for \"{$event->quotation->service->name}\" was rejected.",
        ]);
    }

    public function handleBookingConfirmed(BookingConfirmed $event): void
    {
        $vendorOwnerId = $event->booking->service->vendor->owner_id;

        // Don't notify a member of their own action.
        if ($vendorOwnerId === $event->actor->id) {
            return;
        }

        if (! User::find($vendorOwnerId)?->wantsNotification('booking_confirmed')) {
            return;
        }

        Notification::create([
            'user_id' => $vendorOwnerId,
            'occasion_id' => $event->booking->occasion_id,
            'subject_type' => 'Booking',
            'subject_id' => $event->booking->id,
            'type' => 'booking_confirmed',
            'title' => 'Booking confirmed',
            'body' => "Your Booking for \"{$event->booking->service->name}\" was confirmed.",
        ]);
    }

    public function handleBookingCompleted(BookingCompleted $event): void
    {
        $hostUserId = $event->booking->confirmed_by;

        // Don't notify a member of their own action.
        if ($hostUserId === $event->actor->id) {
            return;
        }

        if (! User::find($hostUserId)?->wantsNotification('booking_completed')) {
            return;
        }

        Notification::create([
            'user_id' => $hostUserId,
            'occasion_id' => $event->booking->occasion_id,
            'subject_type' => 'Booking',
            'subject_id' => $event->booking->id,
            'type' => 'booking_completed',
            'title' => 'Booking completed',
            'body' => "The Vendor marked your Booking for \"{$event->booking->service->name}\" as complete.",
        ]);
    }

    public function handleReviewPublished(ReviewPublished $event): void
    {
        $vendorOwnerId = $event->review->service->vendor->owner_id;

        // Don't notify a member of their own action.
        if ($vendorOwnerId === $event->actor->id) {
            return;
        }

        if (! User::find($vendorOwnerId)?->wantsNotification('review_published')) {
            return;
        }

        Notification::create([
            'user_id' => $vendorOwnerId,
            'occasion_id' => $event->review->occasion_id,
            'subject_type' => 'Review',
            'subject_id' => $event->review->id,
            'type' => 'review_published',
            'title' => 'New review received',
            'body' => "You received a {$event->review->rating}-star review for \"{$event->review->service->name}\".",
        ]);
    }
}
