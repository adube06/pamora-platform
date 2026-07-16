<?php

namespace App\Domains\Finance\Application\Services;

use App\Domains\Finance\Domain\Enums\PledgeStatus;
use App\Domains\Finance\Domain\Events\PledgeStatusUpdated;
use App\Domains\Finance\Domain\Models\Pledge;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;

class UpdatePledgeStatusService
{
    use GuardsAgainstArchivedOccasion;

    public function handle(Pledge $pledge, PledgeStatus $status, User $actor): Pledge
    {
        $this->ensureOccasionAcceptsActivity($pledge->occasion);

        $pledge->update(['status' => $status]);

        PledgeStatusUpdated::dispatch($pledge->fresh(), $actor);

        return $pledge;
    }
}
