<?php

namespace App\Domains\Planning\Presentation\Http\Controllers;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Application\Services\CreateChecklistService;
use App\Domains\Planning\Presentation\Http\Requests\StoreChecklistRequest;
use Illuminate\Http\RedirectResponse;

class ChecklistController
{
    public function store(StoreChecklistRequest $request, Occasion $occasion, CreateChecklistService $service): RedirectResponse
    {
        $service->handle($occasion, $request->validated(), $request->user());

        return back()->with('success', 'Checklist created.');
    }
}
