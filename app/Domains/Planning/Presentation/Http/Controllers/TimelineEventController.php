<?php

namespace App\Domains\Planning\Presentation\Http\Controllers;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Application\Services\ScheduleTimelineEventService;
use App\Domains\Planning\Presentation\Http\Requests\StoreTimelineEventRequest;
use Illuminate\Http\RedirectResponse;

class TimelineEventController
{
    public function store(StoreTimelineEventRequest $request, Occasion $occasion, ScheduleTimelineEventService $service): RedirectResponse
    {
        $service->handle($occasion, $request->validated(), $request->user());

        return back()->with('success', 'Timeline event scheduled.');
    }
}
