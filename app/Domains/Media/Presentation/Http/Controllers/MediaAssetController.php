<?php

namespace App\Domains\Media\Presentation\Http\Controllers;

use App\Domains\Media\Application\Services\MoveMediaAssetService;
use App\Domains\Media\Domain\Models\Album;
use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Media\Presentation\Http\Requests\MoveMediaAssetRequest;
use Illuminate\Http\RedirectResponse;

class MediaAssetController
{
    public function move(MoveMediaAssetRequest $request, MediaAsset $mediaAsset, MoveMediaAssetService $service): RedirectResponse
    {
        $albumId = $request->validated('album_id');
        $album = $albumId !== null ? Album::findOrFail($albumId) : null;

        $service->handle($mediaAsset, $album, $request->user());

        return back()->with('success', 'Media moved.');
    }
}
