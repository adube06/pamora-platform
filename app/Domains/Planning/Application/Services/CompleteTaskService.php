<?php

namespace App\Domains\Planning\Application\Services;

use App\Domains\Planning\Domain\Enums\TaskStatus;
use App\Domains\Planning\Domain\Events\TaskCompleted;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CompleteTaskService
{
    public function handle(Task $task, User $actor): Task
    {
        if ($task->status === TaskStatus::Completed) {
            throw ValidationException::withMessages([
                'status' => 'This task is already completed.',
            ]);
        }

        if ($task->status === TaskStatus::Cancelled) {
            throw ValidationException::withMessages([
                'status' => 'A cancelled task cannot be completed.',
            ]);
        }

        $task->update([
            'status' => TaskStatus::Completed,
            'completed_at' => now(),
        ]);

        TaskCompleted::dispatch($task->fresh(), $actor);

        return $task;
    }
}
