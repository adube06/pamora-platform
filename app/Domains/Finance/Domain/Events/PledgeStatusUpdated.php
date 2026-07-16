<?php

namespace App\Domains\Finance\Domain\Events;

use App\Domains\Finance\Domain\Models\Pledge;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PledgeStatusUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Pledge $pledge,
        public readonly User $actor,
    ) {}
}
