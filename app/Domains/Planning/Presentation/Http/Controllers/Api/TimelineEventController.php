<?php

namespace App\Domains\Planning\Presentation\Http\Controllers\Api;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Application\Services\ScheduleTimelineEventService;
use App\Domains\Planning\Presentation\Http\Requests\StoreTimelineEventRequest;
use App\Domains\Planning\Presentation\Http\Resources\TimelineEventResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimelineEventController
{
    public function index(Request $request, Occasion $occasion): JsonResponse
    {
        $request->user()->can('view', $occasion) || abort(403);

        return response()->json([
            'success' => true,
            'data' => TimelineEventResource::collection($occasion->timelineEvents()->orderBy('scheduled_at')->get()),
        ]);
    }

    public function store(StoreTimelineEventRequest $request, Occasion $occasion, ScheduleTimelineEventService $service): JsonResponse
    {
        $timelineEvent = $service->handle($occasion, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new TimelineEventResource($timelineEvent),
        ], 201);
    }
}
