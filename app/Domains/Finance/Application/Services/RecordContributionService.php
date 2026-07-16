<?php

namespace App\Domains\Finance\Application\Services;

use App\Domains\Finance\Domain\Events\ContributionReceived;
use App\Domains\Finance\Domain\Models\Contribution;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;

class RecordContributionService
{
    use GuardsAgainstArchivedOccasion;

    /**
     * @param  array{contributor_name: string, contributor_phone?: string, amount: string|float, method: string, message?: string, contributed_at: string}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): Contribution
    {
        $this->ensureOccasionAcceptsActivity($occasion);

        $contribution = Contribution::create([
            ...$data,
            'occasion_id' => $occasion->id,
            'recorded_by' => $actor->id,
            'currency' => $data['currency'] ?? 'TZS',
        ]);

        ContributionReceived::dispatch($contribution, $actor);

        return $contribution;
    }
}
