<?php

namespace App\Domains\Finance\Presentation\Http\Controllers\Api;

use App\Domains\Finance\Application\Services\GetContributionSummaryService;
use App\Domains\Finance\Application\Services\RecordContributionService;
use App\Domains\Finance\Presentation\Http\Requests\StoreContributionRequest;
use App\Domains\Finance\Presentation\Http\Resources\ContributionResource;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ContributionController
{
    public function index(Request $request, Occasion $occasion, GetContributionSummaryService $summaryService): JsonResponse
    {
        Gate::authorize('view', $occasion);

        return ApiResponse::success(
            ContributionResource::collection($occasion->contributions()->latest('contributed_at')->get()),
            meta: ['summary' => $summaryService->handle($occasion)],
        );
    }

    public function store(StoreContributionRequest $request, Occasion $occasion, RecordContributionService $service): JsonResponse
    {
        $contribution = $service->handle($occasion, $request->validated(), $request->user());

        return ApiResponse::success(new ContributionResource($contribution), 201);
    }
}
