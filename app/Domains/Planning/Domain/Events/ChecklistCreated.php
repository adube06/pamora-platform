<?php

namespace App\Domains\Planning\Domain\Events;

use App\Domains\Planning\Domain\Models\Checklist;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Matches the Planning PRD's Domain Events section exactly
 * (pamora-foundation/02-product/prd/03-planning.md).
 */
class ChecklistCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Checklist $checklist,
        public readonly User $actor,
    ) {}
}
