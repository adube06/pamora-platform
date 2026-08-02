<?php

namespace App\Domains\Finance\Presentation\Http\Controllers\Api;

use App\Domains\Finance\Application\Services\RecordPledgeService;
use App\Domains\Finance\Application\Services\UpdatePledgeStatusService;
use App\Domains\Finance\Domain\Enums\PledgeStatus;
use App\Domains\Finance\Domain\Models\Pledge;
use App\Domains\Finance\Presentation\Http\Requests\StorePledgeRequest;
use App\Domains\Finance\Presentation\Http\Requests\UpdatePledgeStatusRequest;
use App\Domains\Finance\Presentation\Http\Resources\PledgeResource;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PledgeController
{
    public function index(Request $request, Occasion $occasion): JsonResponse
    {
        // Pledges are transparency-open to any Occasion member, same as
        // Contributions (FinanceServiceProvider) — not permission-gated.
        Gate::authorize('view', $occasion);

        return ApiResponse::success(PledgeResource::collection($occasion->pledges()->latest('pledged_at')->get()));
    }

    public function store(StorePledgeRequest $request, Occasion $occasion, RecordPledgeService $service): JsonResponse
    {
        $pledge = $service->handle($occasion, $request->validated(), $request->user());

        return ApiResponse::success(new PledgeResource($pledge), 201);
    }

    public function update(UpdatePledgeStatusRequest $request, Occasion $occasion, Pledge $pledge, UpdatePledgeStatusService $service): JsonResponse
    {
        $pledge = $service->handle($pledge, PledgeStatus::from($request->validated('status')), $request->user());

        return ApiResponse::success(new PledgeResource($pledge->fresh()));
    }
}
