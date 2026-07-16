<?php

namespace App\Domains\Insights\Application\Services;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Enums\TaskStatus;

/**
 * ADR-008 (Analytics Is Read-Only): computed fresh from Task rows every
 * call, nothing stored — same shape as GetReadinessScoreService and
 * GetBudgetSummaryService. A superset of the single number
 * GetReadinessScoreService::taskCompletionSignal() folds into the
 * Readiness Score, exposed here as its own dashboard metric.
 *
 * @phpstan-type TaskProgress array{
 *     total: int,
 *     draft: int,
 *     open: int,
 *     in_progress: int,
 *     completed: int,
 *     deferred: int,
 *     completion_percentage: ?int,
 * }
 */
class GetTaskProgressService
{
    /**
     * @return TaskProgress
     */
    public function handle(Occasion $occasion): array
    {
        // Cancelled tasks are excluded from the denominator, matching
        // GetReadinessScoreService::taskCompletionSignal()'s own convention.
        $statusCounts = $occasion->tasks()
            ->where('status', '!=', TaskStatus::Cancelled)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $draft = (int) ($statusCounts[TaskStatus::Draft->value] ?? 0);
        $open = (int) ($statusCounts[TaskStatus::Open->value] ?? 0);
        $inProgress = (int) ($statusCounts[TaskStatus::InProgress->value] ?? 0);
        $completed = (int) ($statusCounts[TaskStatus::Completed->value] ?? 0);
        $deferred = (int) ($statusCounts[TaskStatus::Deferred->value] ?? 0);
        $total = $draft + $open + $inProgress + $completed + $deferred;

        return [
            'total' => $total,
            'draft' => $draft,
            'open' => $open,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'deferred' => $deferred,
            'completion_percentage' => $total > 0 ? (int) round(($completed / $total) * 100) : null,
        ];
    }
}
