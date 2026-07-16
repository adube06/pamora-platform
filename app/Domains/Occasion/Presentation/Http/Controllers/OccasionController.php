<?php

namespace App\Domains\Occasion\Presentation\Http\Controllers;

use App\Domains\Finance\Application\Services\GetBudgetSummaryService;
use App\Domains\Insights\Application\Services\GetReadinessScoreService;
use App\Domains\Insights\Application\Services\GetTaskProgressService;
use App\Domains\Occasion\Application\Services\ArchiveOccasionService;
use App\Domains\Occasion\Application\Services\CancelOccasionService;
use App\Domains\Occasion\Application\Services\CreateOccasionService;
use App\Domains\Occasion\Application\Services\UpdateOccasionService;
use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Enums\OccasionType;
use App\Domains\Occasion\Domain\Enums\OccasionVisibility;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Occasion\Presentation\Http\Requests\ArchiveOccasionRequest;
use App\Domains\Occasion\Presentation\Http\Requests\CancelOccasionRequest;
use App\Domains\Occasion\Presentation\Http\Requests\StoreOccasionRequest;
use App\Domains\Occasion\Presentation\Http\Requests\UpdateOccasionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class OccasionController
{
    public function index(Request $request): Response
    {
        $occasions = Occasion::query()
            ->whereHas('members', fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest()
            ->get(['id', 'uuid', 'slug', 'title', 'type', 'status', 'primary_date']);

        return Inertia::render('Occasions/Index', [
            'occasions' => $occasions,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Occasions/Create', [
            'types' => array_map(fn (OccasionType $t) => ['value' => $t->value, 'label' => $t->label()], OccasionType::cases()),
            'visibilities' => array_map(fn (OccasionVisibility $v) => ['value' => $v->value, 'label' => $v->label()], OccasionVisibility::cases()),
        ]);
    }

    public function store(StoreOccasionRequest $request, CreateOccasionService $service): RedirectResponse
    {
        $occasion = $service->handle($request->validated(), $request->user());

        return redirect()->route('occasions.show', $occasion->slug);
    }

    public function show(
        Request $request,
        Occasion $occasion,
        GetReadinessScoreService $readinessService,
        GetBudgetSummaryService $budgetSummaryService,
        GetTaskProgressService $taskProgressService,
    ): Response {
        $request->user()->can('view', $occasion) || abort(403);

        // Funding progress is withheld from the Readiness signals (and the
        // Financial Summary card) for viewers without finance.view_budget,
        // same boundary FinanceController enforces for Budget figures
        // directly (Slice 002.1 Design Decision 7).
        $includeFinance = $request->user()->can('view-budget', $occasion);
        $financialSummary = $budgetSummaryService->handle($occasion);

        return Inertia::render('Occasions/Show', [
            'occasion' => $occasion,
            'member' => $occasion->memberFor($request->user()),
            'readiness' => $readinessService->handle($occasion, $includeFinance),
            'taskProgress' => $taskProgressService->handle($occasion),
            'financialSummary' => $includeFinance ? $financialSummary : Arr::only($financialSummary, ['total_received', 'contribution_count']),
            'canViewBudget' => $includeFinance,
            'canEdit' => $request->user()->can('update', $occasion),
            'canArchive' => $request->user()->can('archive', $occasion),
            'canCancel' => $request->user()->can('cancel', $occasion),
            'types' => array_map(fn (OccasionType $t) => ['value' => $t->value, 'label' => $t->label()], OccasionType::cases()),
            'visibilities' => array_map(fn (OccasionVisibility $v) => ['value' => $v->value, 'label' => $v->label()], OccasionVisibility::cases()),
            // Only statuses the current one may legally move to (plus
            // itself, meaning "no change") — keeps the Edit form from ever
            // offering an illegal transition in the first place.
            'nextStatuses' => collect(OccasionStatus::cases())
                ->filter(fn (OccasionStatus $s) => $s === $occasion->status || $occasion->status->canTransitionTo($s))
                ->map(fn (OccasionStatus $s) => ['value' => $s->value, 'label' => $s->label()])
                ->values(),
        ]);
    }

    public function update(UpdateOccasionRequest $request, Occasion $occasion, UpdateOccasionService $service): RedirectResponse
    {
        $service->handle($occasion, $request->validated(), $request->user());

        return back()->with('success', 'Occasion updated.');
    }

    public function archive(ArchiveOccasionRequest $request, Occasion $occasion, ArchiveOccasionService $service): RedirectResponse
    {
        $service->handle($occasion, $request->user());

        return back()->with('success', 'Occasion archived.');
    }

    public function cancel(CancelOccasionRequest $request, Occasion $occasion, CancelOccasionService $service): RedirectResponse
    {
        $service->handle($occasion, $request->user());

        return back()->with('success', 'Occasion cancelled.');
    }
}
