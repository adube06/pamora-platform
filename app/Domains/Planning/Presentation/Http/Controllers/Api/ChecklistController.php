<?php

namespace App\Domains\Planning\Presentation\Http\Controllers\Api;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Application\Services\CreateChecklistService;
use App\Domains\Planning\Presentation\Http\Requests\StoreChecklistRequest;
use App\Domains\Planning\Presentation\Http\Resources\ChecklistResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChecklistController
{
    public function index(Request $request, Occasion $occasion): JsonResponse
    {
        $request->user()->can('view', $occasion) || abort(403);

        return response()->json([
            'success' => true,
            'data' => ChecklistResource::collection($occasion->checklists()->latest()->get()),
        ]);
    }

    public function store(StoreChecklistRequest $request, Occasion $occasion, CreateChecklistService $service): JsonResponse
    {
        $checklist = $service->handle($occasion, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new ChecklistResource($checklist),
        ], 201);
    }
}
