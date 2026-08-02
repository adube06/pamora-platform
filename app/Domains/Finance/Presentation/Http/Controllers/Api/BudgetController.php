<?php

namespace App\Domains\Finance\Presentation\Http\Controllers\Api;

use App\Domains\Finance\Application\Services\CreateBudgetService;
use App\Domains\Finance\Presentation\Http\Requests\StoreBudgetRequest;
use App\Domains\Finance\Presentation\Http\Resources\BudgetResource;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BudgetController
{
    public function show(Request $request, Occasion $occasion): JsonResponse
    {
        Gate::authorize('view-budget', $occasion);

        return ApiResponse::success(
            $occasion->budget ? new BudgetResource($occasion->budget->load('categories.budgetItems')) : null
        );
    }

    public function store(StoreBudgetRequest $request, Occasion $occasion, CreateBudgetService $service): JsonResponse
    {
        $budget = $service->handle($occasion, $request->validated(), $request->user());

        return ApiResponse::success(new BudgetResource($budget->load('categories.budgetItems')), 201);
    }
}
