<?php

namespace App\Domains\Occasion\Presentation\Http\Controllers;

use App\Domains\Insights\Application\Services\GetReadinessScoreService;
use App\Domains\Occasion\Application\Services\CreateOccasionService;
use App\Domains\Occasion\Domain\Enums\OccasionType;
use App\Domains\Occasion\Domain\Enums\OccasionVisibility;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Occasion\Presentation\Http\Requests\StoreOccasionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function show(Request $request, Occasion $occasion, GetReadinessScoreService $readinessService): Response
    {
        $request->user()->can('view', $occasion) || abort(403);

        // Funding progress is withheld from the Readiness signals for
        // viewers without finance.view_budget, same boundary FinanceController
        // enforces for Budget figures directly (Slice 002.1 Design Decision 7).
        $includeFinance = $request->user()->can('view-budget', $occasion);

        return Inertia::render('Occasions/Show', [
            'occasion' => $occasion,
            'member' => $occasion->memberFor($request->user()),
            'readiness' => $readinessService->handle($occasion, $includeFinance),
        ]);
    }
}
