<?php

namespace App\Domains\Planning\Application\Services;

use App\Domains\Planning\Domain\Events\TaskUpdated;
use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;

class UpdateTaskService
{
    use GuardsAgainstArchivedOccasion;

    /**
     * @param  array{title: string, description?: string|null, priority?: string|null, due_date?: string|null, checklist_id?: int|null}  $data
     */
    public function handle(Task $task, array $data, User $actor): Task
    {
        $this->ensureOccasionAcceptsActivity($task->occasion);

        $task->update($data);

        TaskUpdated::dispatch($task->fresh(), $actor);

        return $task;
    }
}
