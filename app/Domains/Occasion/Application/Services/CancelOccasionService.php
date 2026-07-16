<?php

namespace App\Domains\Occasion\Application\Services;

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Events\OccasionCancelled;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CancelOccasionService
{
    private const TERMINAL_STATUSES = [
        OccasionStatus::Completed,
        OccasionStatus::Archived,
        OccasionStatus::Cancelled,
    ];

    public function handle(Occasion $occasion, User $actor): Occasion
    {
        if (in_array($occasion->status, self::TERMINAL_STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => 'An Occasion may only be cancelled before it is completed.',
            ]);
        }

        $occasion->update([
            'status' => OccasionStatus::Cancelled,
            'updated_by' => $actor->id,
        ]);

        OccasionCancelled::dispatch($occasion->fresh(), $actor);

        return $occasion;
    }
}
