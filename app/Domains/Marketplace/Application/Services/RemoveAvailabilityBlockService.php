<?php

namespace App\Domains\Marketplace\Application\Services;

use App\Domains\Marketplace\Domain\Events\AvailabilityBlockRemoved;
use App\Domains\Marketplace\Domain\Models\AvailabilityBlock;
use App\Models\User;

class RemoveAvailabilityBlockService
{
    public function handle(AvailabilityBlock $availabilityBlock, User $actor): void
    {
        $availabilityBlock->delete();

        AvailabilityBlockRemoved::dispatch($availabilityBlock, $actor);
    }
}
