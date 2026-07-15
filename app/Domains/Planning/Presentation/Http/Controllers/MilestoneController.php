<?php

namespace App\Domains\Planning\Presentation\Http\Controllers;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Application\Services\CreateMilestoneService;
use App\Domains\Planning\Presentation\Http\Requests\StoreMilestoneRequest;
use Illuminate\Http\RedirectResponse;

class MilestoneController
{
    public function store(StoreMilestoneRequest $request, Occasion $occasion, CreateMilestoneService $service): RedirectResponse
    {
        $service->handle($occasion, $request->validated(), $request->user());

        return back()->with('success', 'Milestone created.');
    }
}
