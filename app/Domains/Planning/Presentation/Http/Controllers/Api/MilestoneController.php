<?php

namespace App\Domains\Planning\Presentation\Http\Controllers\Api;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Application\Services\CreateMilestoneService;
use App\Domains\Planning\Presentation\Http\Requests\StoreMilestoneRequest;
use App\Domains\Planning\Presentation\Http\Resources\MilestoneResource;
use App\Domains\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MilestoneController
{
    public function index(Request $request, Occasion $occasion): JsonResponse
    {
        Gate::authorize('view', $occasion);

        return ApiResponse::success(
            MilestoneResource::collection($occasion->milestones()->with('tasks')->latest()->get())
        );
    }

    public function store(StoreMilestoneRequest $request, Occasion $occasion, CreateMilestoneService $service): JsonResponse
    {
        $milestone = $service->handle($occasion, $request->validated(), $request->user());

        return ApiResponse::success(new MilestoneResource($milestone->load('tasks')), 201);
    }
}
