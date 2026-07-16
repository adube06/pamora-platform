<?php

namespace App\Domains\Media\Presentation\Http\Controllers;

use App\Domains\Media\Domain\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaDownloadController
{
    public function __invoke(Request $request, MediaAsset $mediaAsset): StreamedResponse
    {
        $request->user()->can('download', $mediaAsset) || abort(403);

        return Storage::disk($mediaAsset->disk)->download($mediaAsset->path, $mediaAsset->file_name);
    }
}
