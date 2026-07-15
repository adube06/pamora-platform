<?php

namespace App\Domains\Finance\Presentation\Http\Controllers;

use App\Domains\Finance\Application\Services\GetContributionSummaryService;
use App\Domains\Finance\Application\Services\RecordContributionService;
use App\Domains\Finance\Presentation\Http\Requests\StoreContributionRequest;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinanceController
{
    public function index(Request $request, Occasion $occasion, GetContributionSummaryService $summaryService): Response
    {
        $request->user()->can('view', $occasion) || abort(403);

        return Inertia::render('Occasions/Finance', [
            'occasion' => $occasion,
            'contributions' => $occasion->contributions()->latest('contributed_at')->get(),
            'summary' => $summaryService->handle($occasion),
            'canRecordContribution' => $request->user()->can('record-contribution', $occasion),
        ]);
    }

    public function store(StoreContributionRequest $request, Occasion $occasion, RecordContributionService $service): RedirectResponse
    {
        $service->handle($occasion, $request->validated(), $request->user());

        return back()->with('success', 'Contribution recorded.');
    }
}
