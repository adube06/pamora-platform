<?php

namespace App\Domains\Finance\Application\Services;

use App\Domains\Finance\Domain\Enums\PledgeStatus;
use App\Domains\Finance\Domain\Events\PledgeRecorded;
use App\Domains\Finance\Domain\Models\Pledge;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;

class RecordPledgeService
{
    use GuardsAgainstArchivedOccasion;

    /**
     * @param  array{pledgor_name: string, pledgor_phone?: string, amount: string|float, status?: string, message?: string, pledged_at: string}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): Pledge
    {
        $this->ensureOccasionAcceptsActivity($occasion);

        $pledge = Pledge::create([
            ...$data,
            'occasion_id' => $occasion->id,
            'status' => $data['status'] ?? PledgeStatus::Pending,
            'recorded_by' => $actor->id,
            'currency' => $data['currency'] ?? 'TZS',
        ]);

        PledgeRecorded::dispatch($pledge, $actor);

        return $pledge;
    }
}
