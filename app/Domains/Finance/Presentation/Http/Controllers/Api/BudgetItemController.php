<?php

namespace App\Domains\Finance\Presentation\Http\Controllers\Api;

use App\Domains\Finance\Application\Services\AddBudgetItemService;
use App\Domains\Finance\Presentation\Http\Requests\StoreBudgetItemRequest;
use App\Domains\Finance\Presentation\Http\Resources\BudgetItemResource;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\JsonResponse;

class BudgetItemController
{
    public function store(StoreBudgetItemRequest $request, Occasion $occasion, AddBudgetItemService $service): JsonResponse
    {
        $budgetItem = $service->handle($occasion, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new BudgetItemResource($budgetItem->load('category')),
        ], 201);
    }
}
