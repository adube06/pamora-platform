<?php

namespace App\Domains\Planning\Presentation\Http\Controllers\Api;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Application\Services\CreateMilestoneService;
use App\Domains\Planning\Presentation\Http\Requests\StoreMilestoneRequest;
use App\Domains\Planning\Presentation\Http\Resources\MilestoneResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MilestoneController
{
    public function index(Request $request, Occasion $occasion): JsonResponse
    {
        $request->user()->can('view', $occasion) || abort(403);

        return response()->json([
            'success' => true,
            'data' => MilestoneResource::collection($occasion->milestones()->with('tasks')->latest()->get()),
        ]);
    }

    public function store(StoreMilestoneRequest $request, Occasion $occasion, CreateMilestoneService $service): JsonResponse
    {
        $milestone = $service->handle($occasion, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new MilestoneResource($milestone->load('tasks')),
        ], 201);
    }
}
