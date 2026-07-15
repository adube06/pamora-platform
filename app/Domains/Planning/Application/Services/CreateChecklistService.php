<?php

namespace App\Domains\Planning\Application\Services;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Events\ChecklistCreated;
use App\Domains\Planning\Domain\Models\Checklist;
use App\Models\User;

class CreateChecklistService
{
    /**
     * @param  array{name: string}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): Checklist
    {
        $checklist = Checklist::create([
            ...$data,
            'occasion_id' => $occasion->id,
            'created_by' => $actor->id,
        ]);

        ChecklistCreated::dispatch($checklist, $actor);

        return $checklist;
    }
}
