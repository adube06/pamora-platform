<?php

namespace App\Domains\Planning\Presentation\Policies;

use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Shared\Domain\Enums\Permission;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $task->occasion->memberFor($user) !== null;
    }

    public function update(User $user, Task $task): bool
    {
        return $task->occasion->memberFor($user)?->hasPermission(Permission::PlanningEditTask) ?? false;
    }

    public function assign(User $user, Task $task): bool
    {
        return $task->occasion->memberFor($user)?->hasPermission(Permission::PlanningAssignTask) ?? false;
    }
}
