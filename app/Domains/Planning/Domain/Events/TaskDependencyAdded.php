<?php

namespace App\Domains\Planning\Domain\Events;

use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskDependencyAdded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Task $task,
        public readonly Task $dependsOnTask,
        public readonly User $actor,
    ) {}
}
