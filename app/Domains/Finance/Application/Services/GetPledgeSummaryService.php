<?php

namespace App\Domains\Finance\Application\Services;

use App\Domains\Finance\Domain\Enums\PledgeStatus;
use App\Domains\Finance\Domain\Models\Pledge;
use App\Domains\Occasion\Domain\Models\Occasion;

/**
 * ADR-004 (ledger-first), same as GetContributionSummaryService: totals
 * are computed fresh from the pledges rows on every call, never stored.
 *
 * @phpstan-type PledgeSummary array{total_confirmed: string, total_pending: string, pledge_count: int}
 */
class GetPledgeSummaryService
{
    /**
     * @return PledgeSummary
     */
    public function handle(Occasion $occasion): array
    {
        $rows = Pledge::query()
            ->where('occasion_id', $occasion->id)
            ->selectRaw('status, COALESCE(SUM(amount), 0) as total, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->keyBy(fn (Pledge $row) => $row->status->value);

        $confirmed = $rows->get(PledgeStatus::Confirmed->value);
        $pending = $rows->get(PledgeStatus::Pending->value);

        return [
            'total_confirmed' => (string) ($confirmed->total ?? 0),
            'total_pending' => (string) ($pending->total ?? 0),
            'pledge_count' => (int) $rows->sum('count'),
        ];
    }
}
