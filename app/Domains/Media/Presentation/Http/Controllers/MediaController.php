<?php

namespace App\Domains\Media\Presentation\Http\Controllers;

use App\Domains\Media\Application\Services\UploadMediaService;
use App\Domains\Media\Presentation\Http\Requests\StoreMediaAssetRequest;
use App\Domains\Media\Presentation\Http\Resources\MediaAssetResource;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MediaController
{
    public function index(Request $request, Occasion $occasion): Response
    {
        $request->user()->can('view', $occasion) || abort(403);

        return Inertia::render('Occasions/Media', [
            'occasion' => $occasion,
            // Mapped through resolve() per resource rather than
            // MediaAssetResource::collection() — Inertia's prop
            // resolution calls toResponse() on ResourceCollection
            // instances, which would wrap this in {"data": [...]}.
            'mediaAssets' => $occasion->media()->with('uploadedBy:id,name')->latest()->get()
                ->map(fn ($mediaAsset) => (new MediaAssetResource($mediaAsset))->resolve()),
            'canUploadMedia' => $request->user()->can('upload-media', $occasion),
        ]);
    }

    public function store(StoreMediaAssetRequest $request, Occasion $occasion, UploadMediaService $service): RedirectResponse
    {
        $service->handle($occasion, $request->file('file'), $request->validated(), $request->user());

        return back()->with('success', 'File uploaded.');
    }
}
