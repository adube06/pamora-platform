<?php

namespace App\Domains\Finance\Presentation\Http\Controllers\Api;

use App\Domains\Finance\Application\Services\CreateBudgetService;
use App\Domains\Finance\Presentation\Http\Requests\StoreBudgetRequest;
use App\Domains\Finance\Presentation\Http\Resources\BudgetResource;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController
{
    public function show(Request $request, Occasion $occasion): JsonResponse
    {
        $request->user()->can('view-budget', $occasion) || abort(403);

        return response()->json([
            'success' => true,
            'data' => $occasion->budget ? new BudgetResource($occasion->budget->load('categories')) : null,
        ]);
    }

    public function store(StoreBudgetRequest $request, Occasion $occasion, CreateBudgetService $service): JsonResponse
    {
        $budget = $service->handle($occasion, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new BudgetResource($budget->load('categories')),
        ], 201);
    }
}
