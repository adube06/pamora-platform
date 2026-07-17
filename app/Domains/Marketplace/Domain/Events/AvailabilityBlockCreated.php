<?php

namespace App\Domains\Marketplace\Domain\Events;

use App\Domains\Marketplace\Domain\Models\AvailabilityBlock;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AvailabilityBlockCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly AvailabilityBlock $availabilityBlock,
        public readonly User $actor,
    ) {}
}
