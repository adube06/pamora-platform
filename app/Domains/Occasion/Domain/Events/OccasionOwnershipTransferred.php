<?php

namespace App\Domains\Occasion\Domain\Events;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OccasionOwnershipTransferred
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Occasion $occasion,
        public readonly User $previousHost,
        public readonly User $newHost,
        public readonly User $actor,
    ) {}
}
