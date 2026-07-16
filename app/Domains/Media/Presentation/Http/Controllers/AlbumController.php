<?php

namespace App\Domains\Media\Presentation\Http\Controllers;

use App\Domains\Media\Application\Services\CreateAlbumService;
use App\Domains\Media\Presentation\Http\Requests\StoreAlbumRequest;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\RedirectResponse;

class AlbumController
{
    public function store(StoreAlbumRequest $request, Occasion $occasion, CreateAlbumService $service): RedirectResponse
    {
        $service->handle($occasion, $request->validated(), $request->user());

        return back()->with('success', 'Album created.');
    }
}
