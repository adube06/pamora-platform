<?php

namespace App\Domains\Planning\Presentation\Http\Controllers;

use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Application\Services\AssignTaskService;
use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Planning\Presentation\Http\Requests\AssignTaskRequest;
use Illuminate\Http\RedirectResponse;

class TaskController
{
    public function assign(AssignTaskRequest $request, Task $task, AssignTaskService $service): RedirectResponse
    {
        $assignee = OccasionMember::findOrFail($request->validated('assignee_id'));

        $service->handle($task, $assignee, $request->user());

        return back()->with('success', 'Task assigned.');
    }
}
