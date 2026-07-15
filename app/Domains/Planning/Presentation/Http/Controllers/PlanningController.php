<?php

namespace App\Domains\Planning\Presentation\Http\Controllers;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Application\Services\CreateTaskService;
use App\Domains\Planning\Presentation\Http\Requests\StoreTaskRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanningController
{
    public function index(Request $request, Occasion $occasion): Response
    {
        $request->user()->can('view', $occasion) || abort(403);

        return Inertia::render('Occasions/Planning', [
            'occasion' => $occasion,
            'tasks' => $occasion->tasks()->with('assignee.user:id,name')->latest()->get(),
            'members' => $occasion->members()->with('user:id,name')->get(),
            'canCreateTask' => $request->user()->can('create-task', $occasion),
        ]);
    }

    public function store(StoreTaskRequest $request, Occasion $occasion, CreateTaskService $service): RedirectResponse
    {
        $service->handle($occasion, $request->validated(), $request->user());

        return back()->with('success', 'Task created.');
    }
}
