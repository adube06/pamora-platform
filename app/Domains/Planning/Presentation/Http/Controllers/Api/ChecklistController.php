<?php

namespace App\Domains\Planning\Presentation\Http\Controllers\Api;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Application\Services\CreateChecklistService;
use App\Domains\Planning\Presentation\Http\Requests\StoreChecklistRequest;
use App\Domains\Planning\Presentation\Http\Resources\ChecklistResource;
use App\Domains\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChecklistController
{
    public function index(Request $request, Occasion $occasion): JsonResponse
    {
        Gate::authorize('view', $occasion);

        return ApiResponse::success(ChecklistResource::collection($occasion->checklists()->latest()->get()));
    }

    public function store(StoreChecklistRequest $request, Occasion $occasion, CreateChecklistService $service): JsonResponse
    {
        $checklist = $service->handle($occasion, $request->validated(), $request->user());

        return ApiResponse::success(new ChecklistResource($checklist), 201);
    }
}
