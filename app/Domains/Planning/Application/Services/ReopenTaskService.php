<?php

namespace App\Domains\Planning\Application\Services;

use App\Domains\Planning\Domain\Enums\TaskStatus;
use App\Domains\Planning\Domain\Events\TaskReopened;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReopenTaskService
{
    public function handle(Task $task, User $actor): Task
    {
        if ($task->status !== TaskStatus::Completed) {
            throw ValidationException::withMessages([
                'status' => 'Only a completed task can be reopened.',
            ]);
        }

        $task->update([
            'status' => TaskStatus::Open,
            'completed_at' => null,
        ]);

        TaskReopened::dispatch($task->fresh(), $actor);

        return $task;
    }
}
