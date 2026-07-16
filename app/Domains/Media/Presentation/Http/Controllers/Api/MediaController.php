<?php

namespace App\Domains\Media\Presentation\Http\Controllers\Api;

use App\Domains\Media\Application\Services\UploadMediaService;
use App\Domains\Media\Presentation\Http\Requests\StoreMediaAssetRequest;
use App\Domains\Media\Presentation\Http\Resources\MediaAssetResource;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController
{
    public function index(Request $request, Occasion $occasion): JsonResponse
    {
        $request->user()->can('view', $occasion) || abort(403);

        return response()->json([
            'success' => true,
            'data' => MediaAssetResource::collection(
                $occasion->media()->with('uploadedBy:id,name')->latest()->get()
            ),
        ]);
    }

    public function store(StoreMediaAssetRequest $request, Occasion $occasion, UploadMediaService $service): JsonResponse
    {
        $mediaAsset = $service->handle($occasion, $request->file('file'), $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new MediaAssetResource($mediaAsset->load('uploadedBy:id,name')),
        ], 201);
    }
}
