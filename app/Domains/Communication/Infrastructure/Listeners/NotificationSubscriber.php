<?php

namespace App\Domains\Communication\Infrastructure\Listeners;

use App\Domains\Communication\Domain\Events\ReminderTriggered;
use App\Domains\Communication\Domain\Models\Notification;
use App\Domains\Finance\Domain\Events\ContributionReceived;
use App\Domains\People\Domain\Events\MemberJoined;
use App\Domains\Planning\Domain\Events\TaskAssigned;
use App\Domains\Planning\Domain\Events\TaskCompleted;

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
}
