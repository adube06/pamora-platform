<?php

namespace App\Filament\Widgets;

use App\Domains\Finance\Domain\Models\Contribution;
use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * "Basic reports" per the MVP's Admin Portal scope — platform-wide
 * counts computed fresh from live rows, same ADR-008 read-only
 * principle Insights already follows, just at platform scope instead
 * of per-Occasion.
 */
class PlatformStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalOccasions = Occasion::count();
        $statusBreakdown = Occasion::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $statusSummary = collect(OccasionStatus::cases())
            ->map(fn ($case) => $case->label().': '.((int) ($statusBreakdown[$case->value] ?? 0)))
            ->implode(' · ');
        $totalContributions = (string) Contribution::sum('amount');

        return [
            Stat::make('Total Users', User::count()),
            Stat::make('Total Occasions', $totalOccasions)
                ->description($statusSummary),
            Stat::make('Total Contributions Value', number_format((float) $totalContributions, 2)),
        ];
    }
}
