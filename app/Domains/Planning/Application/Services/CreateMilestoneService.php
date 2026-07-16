<?php

namespace App\Domains\Planning\Application\Services;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Models\Milestone;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;

class CreateMilestoneService
{
    use GuardsAgainstArchivedOccasion;

    /**
     * @param  array{name: string, task_ids?: array<int, int>}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): Milestone
    {
        $this->ensureOccasionAcceptsActivity($occasion);

        $milestone = Milestone::create([
            'occasion_id' => $occasion->id,
            'name' => $data['name'],
            'created_by' => $actor->id,
        ]);

        if (! empty($data['task_ids'])) {
            $milestone->tasks()->sync($data['task_ids']);
        }

        return $milestone;
    }
}
