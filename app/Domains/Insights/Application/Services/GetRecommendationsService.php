<?php

namespace App\Domains\Insights\Application\Services;

use App\Domains\Finance\Application\Services\GetBudgetSummaryService;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Enums\TaskStatus;

/**
 * ADR-008 (Analytics Is Read-Only) + FR-006 ("Recommendations must
 * explain why they are shown"): every recommendation carries both the
 * message and the reason it fired. Advisory only — nothing here writes
 * to Tasks, Budgets, or any other domain's records. Skips the PRD's
 * vendor-confirmation example (no Marketplace domain exists yet).
 *
 * @phpstan-type Recommendation array{message: string, reason: string, severity: string}
 */
class GetRecommendationsService
{
    public function __construct(
        private readonly GetParticipationService $participationService,
        private readonly GetBudgetSummaryService $budgetSummaryService,
    ) {}

    /**
     * @return list<Recommendation>
     */
    public function handle(Occasion $occasion, bool $includeFinance): array
    {
        $recommendations = [];

        if ($overdue = $this->overdueTasksRecommendation($occasion)) {
            $recommendations[] = $overdue;
        }

        if ($includeFinance && $budget = $this->budgetExceededRecommendation($occasion)) {
            $recommendations[] = $budget;
        }

        if ($invitations = $this->lowInvitationResponseRecommendation($occasion)) {
            $recommendations[] = $invitations;
        }

        return $recommendations;
    }

    /**
     * @return Recommendation|null
     */
    private function overdueTasksRecommendation(Occasion $occasion): ?array
    {
        $count = $occasion->tasks()
            ->where('due_date', '<', now())
            ->whereNotIn('status', [TaskStatus::Completed, TaskStatus::Cancelled])
            ->count();

        if ($count === 0) {
            return null;
        }

        return [
            'message' => "{$count} task".($count === 1 ? '' : 's').' overdue.',
            'reason' => 'These tasks have a due date in the past and are not yet completed.',
            'severity' => $count >= 3 ? 'critical' : 'warning',
        ];
    }

    /**
     * @return Recommendation|null
     */
    private function budgetExceededRecommendation(Occasion $occasion): ?array
    {
        $summary = $this->budgetSummaryService->handle($occasion);

        if ($summary['spending_progress'] === null || $summary['spending_progress'] <= 100) {
            return null;
        }

        return [
            'message' => 'Budget exceeds the planned amount.',
            'reason' => "Expenses have reached {$summary['spending_progress']}% of the planned budget.",
            'severity' => 'critical',
        ];
    }

    /**
     * @return Recommendation|null
     */
    private function lowInvitationResponseRecommendation(Occasion $occasion): ?array
    {
        $participation = $this->participationService->handle($occasion);

        // Avoid noise on a single pending invite skewing the rate.
        if ($participation['total_invitations'] < 3 || $participation['invitation_acceptance_rate'] === null) {
            return null;
        }

        if ($participation['invitation_acceptance_rate'] >= 50) {
            return null;
        }

        return [
            'message' => 'Invitation response rate is below target.',
            'reason' => "Only {$participation['invitation_acceptance_rate']}% of invitations sent have been accepted.",
            'severity' => 'warning',
        ];
    }
}
