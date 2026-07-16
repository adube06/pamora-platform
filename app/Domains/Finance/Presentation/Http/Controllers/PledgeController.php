<?php

namespace App\Domains\Finance\Presentation\Http\Controllers;

use App\Domains\Finance\Application\Services\RecordPledgeService;
use App\Domains\Finance\Application\Services\UpdatePledgeStatusService;
use App\Domains\Finance\Domain\Enums\PledgeStatus;
use App\Domains\Finance\Domain\Models\Pledge;
use App\Domains\Finance\Presentation\Http\Requests\StorePledgeRequest;
use App\Domains\Finance\Presentation\Http\Requests\UpdatePledgeStatusRequest;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\RedirectResponse;

class PledgeController
{
    public function store(StorePledgeRequest $request, Occasion $occasion, RecordPledgeService $service): RedirectResponse
    {
        $service->handle($occasion, $request->validated(), $request->user());

        return back()->with('success', 'Pledge recorded.');
    }

    public function update(UpdatePledgeStatusRequest $request, Occasion $occasion, Pledge $pledge, UpdatePledgeStatusService $service): RedirectResponse
    {
        $service->handle($pledge, PledgeStatus::from($request->validated('status')), $request->user());

        return back()->with('success', 'Pledge updated.');
    }
}
