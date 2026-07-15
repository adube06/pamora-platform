<?php

namespace App\Domains\Planning\Application\Services;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Enums\TaskStatus;
use App\Domains\Planning\Domain\Events\TaskCreated;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;

class CreateTaskService
{
    /**
     * @param  array{title: string, description?: string, priority?: string, due_date?: string}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): Task
    {
        $task = Task::create([
            ...$data,
            'occasion_id' => $occasion->id,
            'status' => TaskStatus::Open,
            'created_by' => $actor->id,
        ]);

        TaskCreated::dispatch($task, $actor);

        return $task;
    }
}
