<?php

namespace App\Domains\Media\Presentation\Http\Controllers\Api;

use App\Domains\Media\Application\Services\CreateAlbumService;
use App\Domains\Media\Presentation\Http\Requests\StoreAlbumRequest;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\JsonResponse;

class AlbumController
{
    public function store(StoreAlbumRequest $request, Occasion $occasion, CreateAlbumService $service): JsonResponse
    {
        $album = $service->handle($occasion, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => $album,
        ], 201);
    }
}
