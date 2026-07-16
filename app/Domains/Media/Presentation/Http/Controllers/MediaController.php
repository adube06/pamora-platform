<?php

namespace App\Domains\Media\Presentation\Http\Controllers;

use App\Domains\Media\Application\Services\UploadMediaService;
use App\Domains\Media\Presentation\Http\Requests\StoreMediaAssetRequest;
use App\Domains\Media\Presentation\Http\Resources\MediaAssetResource;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Enums\Permission;
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
            'mediaAssets' => $occasion->media()->with(['uploadedBy:id,name', 'attachable'])->latest()->get()
                ->map(fn ($mediaAsset) => (new MediaAssetResource($mediaAsset))->resolve()),
            'albums' => $occasion->albums()->withCount('mediaAssets')->latest()->get(),
            'tasks' => $occasion->tasks()->select('id', 'uuid', 'title')->get(),
            'expenses' => $occasion->expenses()->select('id', 'uuid', 'description', 'amount', 'currency')->get(),
            'announcements' => $occasion->announcements()->select('id', 'uuid', 'title')->get(),
            'canUploadMedia' => $request->user()->can('upload-media', $occasion),
            'canEditMediaMetadata' => $occasion->memberFor($request->user())?->hasPermission(Permission::MediaEditMetadata) ?? false,
        ]);
    }

    public function store(StoreMediaAssetRequest $request, Occasion $occasion, UploadMediaService $service): RedirectResponse
    {
        $service->handle($occasion, $request->file('file'), $request->validated(), $request->user());

        return back()->with('success', 'File uploaded.');
    }
}
