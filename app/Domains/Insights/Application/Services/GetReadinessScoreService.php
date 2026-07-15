<?php

namespace App\Domains\Insights\Application\Services;

use App\Domains\Finance\Application\Services\GetBudgetSummaryService;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Enums\TaskStatus;

/**
 * ADR-008 (Analytics Is Read-Only): this service only reads Planning's
 * and Finance's data — it never writes to Tasks or Budgets. BR-033
 * ("Readiness Score must always be calculated from live business data
 * ... never manually edited") is satisfied by having nothing stored to
 * edit: every call recomputes from source rows, same as
 * GetBudgetSummaryService.
 *
 * First-pass, two-signal heuristic: the Insights PRD names Milestone
 * completion, Vendor confirmations, RSVP completion, and Timeline
 * health as further Readiness inputs, but none of those domains exist
 * in the product yet. Adding a signal later is additive — nothing
 * here is stored, so no migration is needed.
 *
 * @phpstan-type ReadinessSignal array{key: string, label: string, value: int}
 * @phpstan-type ReadinessScore array{score: ?int, signals: list<ReadinessSignal>}
 */
class GetReadinessScoreService
{
    public function __construct(
        private readonly GetBudgetSummaryService $budgetSummaryService,
    ) {}

    /**
     * @return ReadinessScore
     */
    public function handle(Occasion $occasion, bool $includeFinance): array
    {
        $signals = [];

        $taskSignal = $this->taskCompletionSignal($occasion);
        if ($taskSignal !== null) {
            $signals[] = $taskSignal;
        }

        if ($includeFinance) {
            $fundingSignal = $this->fundingProgressSignal($occasion);
            if ($fundingSignal !== null) {
                $signals[] = $fundingSignal;
            }
        }

        $score = $signals === []
            ? null
            : (int) round(array_sum(array_column($signals, 'value')) / count($signals));

        return [
            'score' => $score,
            'signals' => $signals,
        ];
    }

    /**
     * @return ReadinessSignal|null
     */
    private function taskCompletionSignal(Occasion $occasion): ?array
    {
        $total = $occasion->tasks()->where('status', '!=', TaskStatus::Cancelled)->count();

        if ($total === 0) {
            return null;
        }

        $completed = $occasion->tasks()->where('status', TaskStatus::Completed)->count();

        return [
            'key' => 'task_completion',
            'label' => 'Task Completion',
            'value' => (int) round(($completed / $total) * 100),
        ];
    }

    /**
     * @return ReadinessSignal|null
     */
    private function fundingProgressSignal(Occasion $occasion): ?array
    {
        $summary = $this->budgetSummaryService->handle($occasion);

        if ($summary['planned_amount'] === null || $summary['funding_progress'] === null) {
            return null;
        }

        return [
            'key' => 'funding_progress',
            'label' => 'Funding Progress',
            'value' => (int) min(100, round($summary['funding_progress'])),
        ];
    }
}
