<?php

namespace App\Domains\Occasion\Presentation\Http\Controllers\Api;

use App\Domains\Occasion\Application\Services\ArchiveOccasionService;
use App\Domains\Occasion\Application\Services\CancelOccasionService;
use App\Domains\Occasion\Application\Services\CreateOccasionService;
use App\Domains\Occasion\Application\Services\TransferOwnershipService;
use App\Domains\Occasion\Application\Services\UpdateOccasionService;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Occasion\Presentation\Http\Requests\ArchiveOccasionRequest;
use App\Domains\Occasion\Presentation\Http\Requests\CancelOccasionRequest;
use App\Domains\Occasion\Presentation\Http\Requests\StoreOccasionRequest;
use App\Domains\Occasion\Presentation\Http\Requests\TransferOwnershipRequest;
use App\Domains\Occasion\Presentation\Http\Requests\UpdateOccasionRequest;
use App\Domains\Occasion\Presentation\Http\Resources\OccasionResource;
use App\Domains\People\Domain\Models\OccasionMember;
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

    public function update(UpdateOccasionRequest $request, Occasion $occasion, UpdateOccasionService $service): JsonResponse
    {
        $occasion = $service->handle($occasion, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new OccasionResource($occasion->fresh()),
        ]);
    }

    public function archive(ArchiveOccasionRequest $request, Occasion $occasion, ArchiveOccasionService $service): JsonResponse
    {
        $occasion = $service->handle($occasion, $request->user());

        return response()->json([
            'success' => true,
            'data' => new OccasionResource($occasion->fresh()),
        ]);
    }

    public function cancel(CancelOccasionRequest $request, Occasion $occasion, CancelOccasionService $service): JsonResponse
    {
        $occasion = $service->handle($occasion, $request->user());

        return response()->json([
            'success' => true,
            'data' => new OccasionResource($occasion->fresh()),
        ]);
    }

    public function transferOwnership(TransferOwnershipRequest $request, Occasion $occasion, TransferOwnershipService $service): JsonResponse
    {
        $newHostMember = OccasionMember::where('uuid', $request->validated('member_uuid'))->firstOrFail();

        $occasion = $service->handle($occasion, $newHostMember, $request->user());

        return response()->json([
            'success' => true,
            'data' => new OccasionResource($occasion->fresh()),
        ]);
    }
}
