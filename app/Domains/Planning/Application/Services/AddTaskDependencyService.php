<?php

namespace App\Domains\Planning\Application\Services;

use App\Domains\Planning\Domain\Events\TaskDependencyAdded;
use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AddTaskDependencyService
{
    use GuardsAgainstArchivedOccasion;

    public function handle(Task $task, Task $dependsOnTask, User $actor): void
    {
        $this->ensureOccasionAcceptsActivity($task->occasion);

        if ($task->dependencies()->where('depends_on_task_id', $dependsOnTask->id)->exists()) {
            throw ValidationException::withMessages([
                'depends_on_task_id' => 'This Task already depends on that Task.',
            ]);
        }

        if ($this->wouldCreateCycle($task, $dependsOnTask)) {
            throw ValidationException::withMessages([
                'depends_on_task_id' => 'This would create a circular dependency between Tasks.',
            ]);
        }

        $task->dependencies()->attach($dependsOnTask->id);

        TaskDependencyAdded::dispatch($task, $dependsOnTask, $actor);
    }

    /**
     * Adding the edge task -> dependsOnTask creates a cycle if $task is
     * already reachable by walking $dependsOnTask's own dependency chain.
     */
    private function wouldCreateCycle(Task $task, Task $dependsOnTask): bool
    {
        $visited = [];
        $stack = [$dependsOnTask->id];

        while ($stack !== []) {
            $currentId = array_pop($stack);

            if ($currentId === $task->id) {
                return true;
            }

            if (in_array($currentId, $visited, true)) {
                continue;
            }

            $visited[] = $currentId;

            $stack = [...$stack, ...Task::query()->findOrFail($currentId)->dependencies()->pluck('tasks.id')->all()];
        }

        return false;
    }
}
