<?php

namespace App\Domains\Marketplace\Presentation\Http\Controllers\Api;

use App\Domains\Marketplace\Application\Services\PublishServiceService;
use App\Domains\Marketplace\Application\Services\UpdateServiceService;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Domains\Marketplace\Presentation\Http\Requests\PublishServiceRequest;
use App\Domains\Marketplace\Presentation\Http\Requests\UpdateServiceRequest;
use App\Domains\Marketplace\Presentation\Http\Resources\ServiceResource;
use Illuminate\Http\JsonResponse;

class ServiceController
{
    public function store(PublishServiceRequest $request, Vendor $vendor, PublishServiceService $service): JsonResponse
    {
        $published = $service->handle($vendor, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new ServiceResource($published),
        ], 201);
    }

    public function update(UpdateServiceRequest $request, Service $service, UpdateServiceService $updateService): JsonResponse
    {
        $updated = $updateService->handle($service, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new ServiceResource($updated),
        ]);
    }
}
