<?php

namespace App\Domains\Occasion\Application\Services;

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Events\OccasionUpdated;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateOccasionService
{
    /**
     * @param  array{title?: string, type?: string, description?: string|null, primary_date?: string|null, timezone?: string|null, location?: string|null, visibility?: string, status?: string}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): Occasion
    {
        if ($occasion->status === OccasionStatus::Archived) {
            throw ValidationException::withMessages([
                'status' => 'An archived Occasion cannot be edited.',
            ]);
        }

        if (isset($data['status'])) {
            $targetStatus = $data['status'] instanceof OccasionStatus ? $data['status'] : OccasionStatus::from($data['status']);

            if ($targetStatus !== $occasion->status && ! $occasion->status->canTransitionTo($targetStatus)) {
                throw ValidationException::withMessages([
                    'status' => "An Occasion cannot move from {$occasion->status->label()} to {$targetStatus->label()}.",
                ]);
            }
        }

        $occasion->update([
            ...$data,
            'updated_by' => $actor->id,
        ]);

        OccasionUpdated::dispatch($occasion->fresh(), $actor);

        return $occasion;
    }
}
