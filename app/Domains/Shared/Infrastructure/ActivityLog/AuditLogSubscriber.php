<?php

namespace App\Domains\Shared\Infrastructure\ActivityLog;

use App\Domains\Finance\Domain\Events\BudgetCreated;
use App\Domains\Finance\Domain\Events\ContributionReceived;
use App\Domains\Finance\Domain\Events\ExpenseRecorded;
use App\Domains\Identity\Domain\Events\UserRegistered;
use App\Domains\Identity\Domain\Events\UserSignedIn;
use App\Domains\Occasion\Domain\Events\OccasionCreated;
use App\Domains\People\Domain\Events\MemberInvited;
use App\Domains\People\Domain\Events\MemberJoined;
use App\Domains\Planning\Domain\Events\ChecklistCreated;
use App\Domains\Planning\Domain\Events\TaskAssigned;
use App\Domains\Planning\Domain\Events\TaskCompleted;
use App\Domains\Planning\Domain\Events\TaskCreated;
use App\Domains\Planning\Domain\Events\TaskReopened;
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
        $events->listen(TaskCreated::class, [self::class, 'handleTaskCreated']);
        $events->listen(TaskAssigned::class, [self::class, 'handleTaskAssigned']);
        $events->listen(TaskCompleted::class, [self::class, 'handleTaskCompleted']);
        $events->listen(TaskReopened::class, [self::class, 'handleTaskReopened']);
        $events->listen(ChecklistCreated::class, [self::class, 'handleChecklistCreated']);
        $events->listen(ContributionReceived::class, [self::class, 'handleContributionReceived']);
        $events->listen(BudgetCreated::class, [self::class, 'handleBudgetCreated']);
        $events->listen(ExpenseRecorded::class, [self::class, 'handleExpenseRecorded']);
    }
}
