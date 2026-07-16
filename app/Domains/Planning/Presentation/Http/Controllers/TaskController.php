<?php

namespace App\Domains\Planning\Presentation\Http\Controllers;

use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Application\Services\AddTaskDependencyService;
use App\Domains\Planning\Application\Services\AssignTaskService;
use App\Domains\Planning\Application\Services\CompleteTaskService;
use App\Domains\Planning\Application\Services\RemoveTaskDependencyService;
use App\Domains\Planning\Application\Services\ReopenTaskService;
use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Planning\Presentation\Http\Requests\AddTaskDependencyRequest;
use App\Domains\Planning\Presentation\Http\Requests\AssignTaskRequest;
use App\Domains\Planning\Presentation\Http\Requests\CompleteTaskRequest;
use App\Domains\Planning\Presentation\Http\Requests\RemoveTaskDependencyRequest;
use App\Domains\Planning\Presentation\Http\Requests\ReopenTaskRequest;
use Illuminate\Http\RedirectResponse;

class TaskController
{
    public function assign(AssignTaskRequest $request, Task $task, AssignTaskService $service): RedirectResponse
    {
        $assignee = OccasionMember::findOrFail($request->validated('assignee_id'));

        $service->handle($task, $assignee, $request->user());

        return back()->with('success', 'Task assigned.');
    }

    public function complete(CompleteTaskRequest $request, Task $task, CompleteTaskService $service): RedirectResponse
    {
        $service->handle($task, $request->user());

        return back()->with('success', 'Task completed.');
    }

    public function reopen(ReopenTaskRequest $request, Task $task, ReopenTaskService $service): RedirectResponse
    {
        $service->handle($task, $request->user());

        return back()->with('success', 'Task reopened.');
    }

    public function addDependency(AddTaskDependencyRequest $request, Task $task, AddTaskDependencyService $service): RedirectResponse
    {
        $dependsOnTask = Task::findOrFail($request->validated('depends_on_task_id'));

        $service->handle($task, $dependsOnTask, $request->user());

        return back()->with('success', 'Dependency added.');
    }

    public function removeDependency(RemoveTaskDependencyRequest $request, Task $task, Task $dependency, RemoveTaskDependencyService $service): RedirectResponse
    {
        $service->handle($task, $dependency, $request->user());

        return back()->with('success', 'Dependency removed.');
    }
}
