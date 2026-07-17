<?php

namespace App\Domains\Marketplace\Application\Services;

use App\Domains\Marketplace\Domain\Events\AvailabilityBlockCreated;
use App\Domains\Marketplace\Domain\Models\AvailabilityBlock;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;

class CreateAvailabilityBlockService
{
    /**
     * @param  array{start_date: string, end_date: string, reason?: string|null}  $data
     */
    public function handle(Vendor $vendor, array $data, User $actor): AvailabilityBlock
    {
        $availabilityBlock = $vendor->availabilityBlocks()->create($data);

        AvailabilityBlockCreated::dispatch($availabilityBlock, $actor);

        return $availabilityBlock;
    }
}
