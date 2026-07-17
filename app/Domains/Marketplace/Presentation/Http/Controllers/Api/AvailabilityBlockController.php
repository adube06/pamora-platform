<?php

namespace App\Domains\Marketplace\Presentation\Http\Controllers\Api;

use App\Domains\Marketplace\Application\Services\CreateAvailabilityBlockService;
use App\Domains\Marketplace\Application\Services\RemoveAvailabilityBlockService;
use App\Domains\Marketplace\Domain\Models\AvailabilityBlock;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Domains\Marketplace\Presentation\Http\Requests\CreateAvailabilityBlockRequest;
use App\Domains\Marketplace\Presentation\Http\Requests\RemoveAvailabilityBlockRequest;
use App\Domains\Marketplace\Presentation\Http\Resources\AvailabilityBlockResource;
use Illuminate\Http\JsonResponse;

class AvailabilityBlockController
{
    public function store(CreateAvailabilityBlockRequest $request, Vendor $vendor, CreateAvailabilityBlockService $service): JsonResponse
    {
        $availabilityBlock = $service->handle($vendor, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new AvailabilityBlockResource($availabilityBlock),
        ], 201);
    }

    public function destroy(RemoveAvailabilityBlockRequest $request, AvailabilityBlock $availabilityBlock, RemoveAvailabilityBlockService $service): JsonResponse
    {
        $service->handle($availabilityBlock, $request->user());

        return response()->json(['success' => true]);
    }
}
