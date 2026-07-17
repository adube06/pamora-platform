<?php

namespace App\Domains\Marketplace\Presentation\Http\Controllers\Api;

use App\Domains\Marketplace\Application\Services\PublishRentalItemService;
use App\Domains\Marketplace\Application\Services\UpdateRentalItemService;
use App\Domains\Marketplace\Domain\Models\RentalItem;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Domains\Marketplace\Presentation\Http\Requests\PublishRentalItemRequest;
use App\Domains\Marketplace\Presentation\Http\Requests\UpdateRentalItemRequest;
use App\Domains\Marketplace\Presentation\Http\Resources\RentalItemResource;
use Illuminate\Http\JsonResponse;

class RentalItemController
{
    public function store(PublishRentalItemRequest $request, Vendor $vendor, PublishRentalItemService $service): JsonResponse
    {
        $published = $service->handle($vendor, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new RentalItemResource($published),
        ], 201);
    }

    public function update(UpdateRentalItemRequest $request, RentalItem $rentalItem, UpdateRentalItemService $updateService): JsonResponse
    {
        $updated = $updateService->handle($rentalItem, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new RentalItemResource($updated),
        ]);
    }
}
