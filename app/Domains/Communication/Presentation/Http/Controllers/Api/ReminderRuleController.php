<?php

namespace App\Domains\Communication\Presentation\Http\Controllers\Api;

use App\Domains\Communication\Application\Services\ScheduleReminderRuleService;
use App\Domains\Communication\Presentation\Http\Requests\StoreReminderRuleRequest;
use App\Domains\Communication\Presentation\Http\Resources\ReminderRuleResource;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReminderRuleController
{
    public function index(Request $request, Occasion $occasion): JsonResponse
    {
        Gate::authorize('view', $occasion);

        return ApiResponse::success(ReminderRuleResource::collection(
            $occasion->reminderRules()->with('timelineEvent')->latest()->get()
        ));
    }

    public function store(StoreReminderRuleRequest $request, Occasion $occasion, ScheduleReminderRuleService $service): JsonResponse
    {
        $reminderRule = $service->handle($occasion, $request->validated(), $request->user());

        return ApiResponse::success(new ReminderRuleResource($reminderRule->load('timelineEvent')), 201);
    }
}
