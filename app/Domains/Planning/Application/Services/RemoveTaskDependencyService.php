<?php

namespace App\Domains\Planning\Application\Services;

use App\Domains\Planning\Domain\Events\TaskDependencyRemoved;
use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RemoveTaskDependencyService
{
    use GuardsAgainstArchivedOccasion;

    public function handle(Task $task, Task $dependsOnTask, User $actor): void
    {
        $this->ensureOccasionAcceptsActivity($task->occasion);

        if (! $task->dependencies()->where('depends_on_task_id', $dependsOnTask->id)->exists()) {
            throw ValidationException::withMessages([
                'depends_on_task_id' => 'This Task does not depend on that Task.',
            ]);
        }

        $task->dependencies()->detach($dependsOnTask->id);

        TaskDependencyRemoved::dispatch($task, $dependsOnTask, $actor);
    }
}
