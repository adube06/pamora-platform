<?php

namespace App\Domains\Finance\Domain\Events;

use App\Domains\Finance\Domain\Models\Contribution;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Matches the Finance PRD's Domain Events section exactly
 * (pamora-foundation/02-product/prd/04-finance.md).
 */
class ContributionReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Contribution $contribution,
        public readonly User $actor,
    ) {}
}
