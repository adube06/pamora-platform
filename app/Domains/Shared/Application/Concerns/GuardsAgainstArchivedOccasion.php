<?php

namespace App\Domains\Shared\Application\Concerns;

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Validation\ValidationException;

/**
 * BR-009: "Archived Occasions cannot receive new activities"
 * (pamora-foundation/02-product/05-business-rules.md). Used by every
 * Application Service that creates a new record against an Occasion —
 * mirrors the archived-check UpdateOccasionService already applies to
 * its own edits.
 */
trait GuardsAgainstArchivedOccasion
{
    protected function ensureOccasionAcceptsActivity(Occasion $occasion): void
    {
        if ($occasion->status === OccasionStatus::Archived) {
            throw ValidationException::withMessages([
                'occasion' => 'This Occasion is archived and cannot receive new activity.',
            ]);
        }
    }
}
