<?php

namespace App\Domains\Finance\Application\Services;

use App\Domains\Finance\Domain\Models\Expense;
use App\Domains\Occasion\Domain\Models\Occasion;

/**
 * ADR-004 (ledger-first) enforced here, same as GetContributionSummaryService:
 * every figure is computed fresh from source rows, never stored. `health`
 * is a first-pass heuristic on spending_progress (the Finance PRD §8 names
 * the four states but doesn't define the formula) — tunable later without
 * a migration since nothing here is persisted.
 *
 * @phpstan-type BudgetSummary array{
 *     planned_amount: ?string,
 *     total_received: string,
 *     contribution_count: int,
 *     total_pledged: string,
 *     pending_pledged: string,
 *     total_expense: string,
 *     remaining_budget: ?string,
 *     funding_progress: ?float,
 *     spending_progress: ?float,
 *     health: ?string,
 * }
 */
class GetBudgetSummaryService
{
    public function __construct(
        private readonly GetContributionSummaryService $contributionSummaryService,
        private readonly GetPledgeSummaryService $pledgeSummaryService,
    ) {}

    /**
     * @return BudgetSummary
     */
    public function handle(Occasion $occasion): array
    {
        $budget = $occasion->budget;
        $contributionSummary = $this->contributionSummaryService->handle($occasion);
        $pledgeSummary = $this->pledgeSummaryService->handle($occasion);
        $totalReceived = $contributionSummary['total_received'];
        $totalPledged = $pledgeSummary['total_confirmed'];
        $totalExpense = (string) Expense::query()->where('occasion_id', $occasion->id)->sum('amount');

        if ($budget === null) {
            return [
                'planned_amount' => null,
                'total_received' => $totalReceived,
                'contribution_count' => $contributionSummary['contribution_count'],
                'total_pledged' => $totalPledged,
                'pending_pledged' => $pledgeSummary['total_pending'],
                'total_expense' => $totalExpense,
                'remaining_budget' => null,
                'funding_progress' => null,
                'spending_progress' => null,
                'health' => null,
            ];
        }

        $planned = (float) $budget->planned_amount;
        $spendingProgress = $planned > 0 ? ((float) $totalExpense / $planned) * 100 : 0.0;
        // Funding Progress = Received Contributions + Confirmed Pledges
        // (Finance PRD §7) — Pending Pledges are surfaced separately
        // (pending_pledged) and deliberately excluded from this figure.
        $fundingProgress = $planned > 0 ? ((float) ($totalReceived + $totalPledged) / $planned) * 100 : 0.0;

        return [
            'planned_amount' => (string) $budget->planned_amount,
            'total_received' => $totalReceived,
            'contribution_count' => $contributionSummary['contribution_count'],
            'total_pledged' => $totalPledged,
            'pending_pledged' => $pledgeSummary['total_pending'],
            'total_expense' => $totalExpense,
            'remaining_budget' => (string) ($planned - (float) $totalExpense),
            'funding_progress' => round($fundingProgress, 1),
            'spending_progress' => round($spendingProgress, 1),
            'health' => $this->health($spendingProgress),
        ];
    }

    private function health(float $spendingProgress): string
    {
        return match (true) {
            $spendingProgress > 100 => 'over_budget',
            $spendingProgress >= 90 => 'at_risk',
            $spendingProgress < 50 => 'under_budget',
            default => 'on_track',
        };
    }
}
