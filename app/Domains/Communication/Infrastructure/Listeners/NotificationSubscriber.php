<?php

namespace App\Domains\Communication\Infrastructure\Listeners;

use App\Domains\Communication\Domain\Models\Notification;
use App\Domains\Planning\Domain\Events\TaskAssigned;

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
}
