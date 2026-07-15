<?php

namespace App\Domains\Planning\Infrastructure\Listeners;

use App\Domains\Planning\Domain\Events\MilestoneCompleted;
use App\Domains\Planning\Domain\Events\TaskCompleted;

/**
 * BR-017 ("Milestones are achieved automatically") detection point.
 * Deliberately a listener on the existing TaskCompleted event, not logic
 * inlined into CompleteTaskService — Planning's own internal cross-cutting
 * concern, handled the same way Shared's AuditLogSubscriber reacts to
 * domain events rather than domains calling it directly (ADR-006).
 *
 * Since CompleteTaskService already rejects completing an already-completed
 * Task, a Milestone can only transition to fully-achieved at the exact
 * moment its last incomplete Task is completed, so checking isAchieved()
 * here is always correct and never re-fires for the same achievement.
 */
class MilestoneAchievementListener
{
    public function handle(TaskCompleted $event): void
    {
        foreach ($event->task->milestones as $milestone) {
            if ($milestone->isAchieved()) {
                MilestoneCompleted::dispatch($milestone, $event->actor);
            }
        }
    }
}
