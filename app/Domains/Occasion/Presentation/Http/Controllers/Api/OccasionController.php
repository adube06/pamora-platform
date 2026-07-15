<?php

namespace App\Domains\Occasion\Presentation\Http\Controllers\Api;

use App\Domains\Occasion\Application\Services\CreateOccasionService;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Occasion\Presentation\Http\Requests\StoreOccasionRequest;
use App\Domains\Occasion\Presentation\Http\Resources\OccasionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OccasionController
{
    public function index(Request $request): JsonResponse
    {
        $occasions = Occasion::query()
            ->whereHas('members', fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => OccasionResource::collection($occasions),
        ]);
    }

    public function store(StoreOccasionRequest $request, CreateOccasionService $service): JsonResponse
    {
        $occasion = $service->handle($request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new OccasionResource($occasion),
        ], 201);
    }

    public function show(Request $request, Occasion $occasion): JsonResponse
    {
        $request->user()->can('view', $occasion) || abort(403);

        return response()->json([
            'success' => true,
            'data' => new OccasionResource($occasion),
        ]);
    }
}
