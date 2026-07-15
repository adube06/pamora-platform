<?php

namespace App\Domains\Planning\Domain\Events;

use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskReopened
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Task $task,
        public readonly User $actor,
    ) {}
}
