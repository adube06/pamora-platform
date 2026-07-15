<?php

namespace App\Domains\Finance\Presentation\Http\Controllers;

use App\Domains\Finance\Application\Services\GetBudgetSummaryService;
use App\Domains\Finance\Application\Services\RecordContributionService;
use App\Domains\Finance\Presentation\Http\Requests\StoreContributionRequest;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class FinanceController
{
    public function index(Request $request, Occasion $occasion, GetBudgetSummaryService $summaryService): Response
    {
        $request->user()->can('view', $occasion) || abort(403);

        // Contribution figures are open by transparency default (Product
        // Philosophy Principle 6); Budget/Expense figures are permission-
        // gated per the Permission Catalog (finance.view_budget), so they
        // are only sent to the frontend when the viewer actually holds it —
        // the API must not leak them just because the UI would hide them.
        $canViewBudget = $request->user()->can('view-budget', $occasion);
        $summary = $summaryService->handle($occasion);

        return Inertia::render('Occasions/Finance', [
            'occasion' => $occasion,
            'budget' => $canViewBudget ? $occasion->budget?->load('categories') : null,
            'contributions' => $occasion->contributions()->latest('contributed_at')->get(),
            'expenses' => $canViewBudget ? $occasion->expenses()->with('category')->latest('spent_at')->get() : [],
            'summary' => $canViewBudget ? $summary : Arr::only($summary, ['total_received', 'contribution_count']),
            'canRecordContribution' => $request->user()->can('record-contribution', $occasion),
            'canViewBudget' => $canViewBudget,
            'canEditBudget' => $request->user()->can('edit-budget', $occasion),
            'canRecordExpense' => $request->user()->can('record-expense', $occasion),
        ]);
    }

    public function store(StoreContributionRequest $request, Occasion $occasion, RecordContributionService $service): RedirectResponse
    {
        $service->handle($occasion, $request->validated(), $request->user());

        return back()->with('success', 'Contribution recorded.');
    }
}
