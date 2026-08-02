<?php

namespace App\Domains\Planning\Application\Services;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Enums\TaskStatus;
use App\Domains\Planning\Domain\Events\TaskCreated;
use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;

class CreateTaskService
{
    use GuardsAgainstArchivedOccasion;

    /**
     * @param  array{title: string, description?: string, priority?: string, due_date?: string}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): Task
    {
        $this->ensureOccasionAcceptsActivity($occasion);

        // Refresh after create(): priority is optional and left to its
        // database column default when omitted — the in-memory model from
        // create() won't reflect that until reloaded (same class of bug
        // Occasion's CreateOccasionService had for `visibility`).
        $task = Task::create([
            ...$data,
            'occasion_id' => $occasion->id,
            'status' => TaskStatus::Open,
            'created_by' => $actor->id,
        ])->fresh();

        TaskCreated::dispatch($task, $actor);

        return $task;
    }
}
