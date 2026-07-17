<?php

namespace App\Domains\Marketplace\Presentation\Http\Controllers\Api;

use App\Domains\Marketplace\Application\Services\ApplyAsVendorService;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Domains\Marketplace\Presentation\Http\Requests\ApplyAsVendorRequest;
use App\Domains\Marketplace\Presentation\Http\Resources\VendorResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController
{
    public function index(Request $request): JsonResponse
    {
        $vendor = Vendor::where('owner_id', $request->user()->id)->first();

        return response()->json([
            'success' => true,
            'data' => $vendor !== null ? new VendorResource($vendor) : null,
        ]);
    }

    public function store(ApplyAsVendorRequest $request, ApplyAsVendorService $service): JsonResponse
    {
        $vendor = $service->handle($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => new VendorResource($vendor),
        ], 201);
    }
}
