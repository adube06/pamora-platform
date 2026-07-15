<?php

namespace App\Domains\Planning\Presentation\Http\Controllers\Api;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Planning\Application\Services\AssignTaskService;
use App\Domains\Planning\Application\Services\CreateTaskService;
use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Planning\Presentation\Http\Requests\AssignTaskRequest;
use App\Domains\Planning\Presentation\Http\Requests\StoreTaskRequest;
use App\Domains\Planning\Presentation\Http\Resources\TaskResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController
{
    public function index(Request $request, Occasion $occasion): JsonResponse
    {
        $request->user()->can('view', $occasion) || abort(403);

        return response()->json([
            'success' => true,
            'data' => TaskResource::collection($occasion->tasks()->latest()->get()),
        ]);
    }

    public function store(StoreTaskRequest $request, Occasion $occasion, CreateTaskService $service): JsonResponse
    {
        $task = $service->handle($occasion, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new TaskResource($task),
        ], 201);
    }

    public function assign(AssignTaskRequest $request, Task $task, AssignTaskService $service): JsonResponse
    {
        $assignee = OccasionMember::findOrFail($request->validated('assignee_id'));

        $task = $service->handle($task, $assignee, $request->user());

        return response()->json([
            'success' => true,
            'data' => new TaskResource($task),
        ]);
    }
}
