<?php

namespace App\Domains\Occasion\Application\Services;

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Events\OccasionArchived;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ArchiveOccasionService
{
    public function handle(Occasion $occasion, User $actor): Occasion
    {
        if ($occasion->status !== OccasionStatus::Completed) {
            throw ValidationException::withMessages([
                'status' => 'Only a completed Occasion may be archived.',
            ]);
        }

        $occasion->update([
            'status' => OccasionStatus::Archived,
            'updated_by' => $actor->id,
        ]);

        OccasionArchived::dispatch($occasion->fresh(), $actor);

        return $occasion;
    }
}
