<?php

namespace App\Domains\Media\Presentation\Http\Controllers\Api;

use App\Domains\Media\Application\Services\MoveMediaAssetService;
use App\Domains\Media\Domain\Models\Album;
use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Media\Presentation\Http\Requests\MoveMediaAssetRequest;
use App\Domains\Media\Presentation\Http\Resources\MediaAssetResource;
use Illuminate\Http\JsonResponse;

class MediaAssetController
{
    public function move(MoveMediaAssetRequest $request, MediaAsset $mediaAsset, MoveMediaAssetService $service): JsonResponse
    {
        $albumId = $request->validated('album_id');
        $album = $albumId !== null ? Album::findOrFail($albumId) : null;

        $mediaAsset = $service->handle($mediaAsset, $album, $request->user());

        return response()->json([
            'success' => true,
            'data' => new MediaAssetResource($mediaAsset->load(['uploadedBy:id,name', 'attachable'])),
        ]);
    }
}
