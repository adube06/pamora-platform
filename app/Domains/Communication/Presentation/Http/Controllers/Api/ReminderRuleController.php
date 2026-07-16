<?php

namespace App\Domains\Communication\Presentation\Http\Controllers\Api;

use App\Domains\Communication\Application\Services\ScheduleReminderRuleService;
use App\Domains\Communication\Presentation\Http\Requests\StoreReminderRuleRequest;
use App\Domains\Communication\Presentation\Http\Resources\ReminderRuleResource;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\JsonResponse;

class ReminderRuleController
{
    public function store(StoreReminderRuleRequest $request, Occasion $occasion, ScheduleReminderRuleService $service): JsonResponse
    {
        $reminderRule = $service->handle($occasion, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new ReminderRuleResource($reminderRule->load('timelineEvent')),
        ], 201);
    }
}
