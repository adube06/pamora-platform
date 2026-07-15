<?php

namespace App\Domains\Planning\Application\Services;

use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Domain\Events\TaskAssigned;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AssignTaskService
{
    public function handle(Task $task, OccasionMember $assignee, User $actor): Task
    {
        // BR-015: a Task may have zero or one assignee, and the assignee
        // must be a member of the same Occasion as the Task.
        if ($assignee->occasion_id !== $task->occasion_id) {
            throw ValidationException::withMessages([
                'assignee_id' => 'The assignee must be a member of this Occasion.',
            ]);
        }

        $task->update(['assignee_id' => $assignee->id]);

        TaskAssigned::dispatch($task->fresh(), $actor);

        return $task;
    }
}
