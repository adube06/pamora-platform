<?php

namespace App\Domains\Planning\Presentation\Http\Controllers;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Application\Services\CreateTaskService;
use App\Domains\Planning\Presentation\Http\Requests\StoreTaskRequest;
use App\Domains\Planning\Presentation\Http\Resources\MilestoneResource;
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
            'checklists' => $occasion->checklists()->latest()->get(),
            // MilestoneResource (not the raw model, unlike every other prop
            // here) since is_achieved is a computed value the Resource
            // layer produces, not a real column. Mapped to resolve() per
            // resource rather than MilestoneResource::collection() directly
            // — Inertia's prop resolution calls toResponse() on
            // ResourceCollection instances, which applies Laravel's
            // automatic "data" wrapping and would nest the array under an
            // extra data key ({"data": [...]}) instead of a bare array.
            'milestones' => $occasion->milestones()->with('tasks:id,uuid,title,status')->latest()->get()
                ->map(fn ($milestone) => (new MilestoneResource($milestone))->resolve()),
            'timelineEvents' => $occasion->timelineEvents()->orderBy('scheduled_at')->get(),
            'members' => $occasion->members()->with('user:id,name')->get(),
            'canCreateTask' => $request->user()->can('create-task', $occasion),
            'canCompleteTask' => $request->user()->can('complete-task', $occasion),
            'canReopenTask' => $request->user()->can('reopen-task', $occasion),
            'canManageChecklist' => $request->user()->can('manage-checklist', $occasion),
            'canManageMilestone' => $request->user()->can('manage-milestone', $occasion),
            'canManageTimeline' => $request->user()->can('manage-timeline', $occasion),
        ]);
    }

    public function store(StoreTaskRequest $request, Occasion $occasion, CreateTaskService $service): RedirectResponse
    {
        $service->handle($occasion, $request->validated(), $request->user());

        return back()->with('success', 'Task created.');
    }
}
