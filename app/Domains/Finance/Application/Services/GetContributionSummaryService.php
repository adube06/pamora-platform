<?php

namespace App\Domains\Finance\Application\Services;

use App\Domains\Finance\Domain\Models\Contribution;
use App\Domains\Occasion\Domain\Models\Occasion;

/**
 * ADR-004 (ledger-first) enforced here: total_received is never stored
 * on Occasion or anywhere else — it is computed fresh from the
 * contributions rows on every call. This is the extension point Slice
 * 002.1 will widen to include Budget-relative figures (Planned Budget,
 * Remaining Budget, Funding Progress) once Budget exists.
 *
 * @phpstan-type ContributionSummary array{total_received: string, contribution_count: int}
 */
class GetContributionSummaryService
{
    /**
     * @return ContributionSummary
     */
    public function handle(Occasion $occasion): array
    {
        $result = Contribution::query()
            ->where('occasion_id', $occasion->id)
            ->selectRaw('COALESCE(SUM(amount), 0) as total, COUNT(*) as count')
            ->first();

        return [
            'total_received' => (string) $result->total,
            'contribution_count' => (int) $result->count,
        ];
    }
}
