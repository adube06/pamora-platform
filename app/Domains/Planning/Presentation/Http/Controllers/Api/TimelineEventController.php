<?php

namespace App\Domains\Planning\Presentation\Http\Controllers\Api;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Application\Services\ScheduleTimelineEventService;
use App\Domains\Planning\Presentation\Http\Requests\StoreTimelineEventRequest;
use App\Domains\Planning\Presentation\Http\Resources\TimelineEventResource;
use App\Domains\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TimelineEventController
{
    public function index(Request $request, Occasion $occasion): JsonResponse
    {
        Gate::authorize('view', $occasion);

        return ApiResponse::success(
            TimelineEventResource::collection($occasion->timelineEvents()->orderBy('scheduled_at')->get())
        );
    }

    public function store(StoreTimelineEventRequest $request, Occasion $occasion, ScheduleTimelineEventService $service): JsonResponse
    {
        $timelineEvent = $service->handle($occasion, $request->validated(), $request->user());

        return ApiResponse::success(new TimelineEventResource($timelineEvent), 201);
    }
}
