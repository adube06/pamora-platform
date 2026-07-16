<?php

namespace App\Domains\Occasion\Domain\Events;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OccasionCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Occasion $occasion,
        public readonly User $actor,
    ) {}
}
