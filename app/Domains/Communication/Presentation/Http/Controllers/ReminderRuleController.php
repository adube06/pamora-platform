<?php

namespace App\Domains\Communication\Presentation\Http\Controllers;

use App\Domains\Communication\Application\Services\ScheduleReminderRuleService;
use App\Domains\Communication\Presentation\Http\Requests\StoreReminderRuleRequest;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\RedirectResponse;

class ReminderRuleController
{
    public function store(StoreReminderRuleRequest $request, Occasion $occasion, ScheduleReminderRuleService $service): RedirectResponse
    {
        $service->handle($occasion, $request->validated(), $request->user());

        return back()->with('success', 'Reminder scheduled.');
    }
}
