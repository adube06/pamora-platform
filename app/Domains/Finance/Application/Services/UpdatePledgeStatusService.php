<?php

namespace App\Domains\Finance\Application\Services;

use App\Domains\Finance\Domain\Enums\PledgeStatus;
use App\Domains\Finance\Domain\Events\PledgeStatusUpdated;
use App\Domains\Finance\Domain\Models\Pledge;
use App\Models\User;

class UpdatePledgeStatusService
{
    public function handle(Pledge $pledge, PledgeStatus $status, User $actor): Pledge
    {
        $pledge->update(['status' => $status]);

        PledgeStatusUpdated::dispatch($pledge->fresh(), $actor);

        return $pledge;
    }
}
