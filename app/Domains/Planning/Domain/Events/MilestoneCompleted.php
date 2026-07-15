<?php

namespace App\Domains\Planning\Domain\Events;

use App\Domains\Planning\Domain\Models\Milestone;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Matches the Planning PRD's Domain Events section exactly
 * (pamora-foundation/02-product/prd/03-planning.md).
 */
class MilestoneCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Milestone $milestone,
        public readonly User $actor,
    ) {}
}
