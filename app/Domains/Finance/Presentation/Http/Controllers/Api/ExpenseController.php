<?php

namespace App\Domains\Finance\Presentation\Http\Controllers\Api;

use App\Domains\Finance\Application\Services\RecordExpenseService;
use App\Domains\Finance\Presentation\Http\Requests\StoreExpenseRequest;
use App\Domains\Finance\Presentation\Http\Resources\ExpenseResource;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController
{
    public function index(Request $request, Occasion $occasion): JsonResponse
    {
        $request->user()->can('view-budget', $occasion) || abort(403);

        return response()->json([
            'success' => true,
            'data' => ExpenseResource::collection($occasion->expenses()->with('category')->latest('spent_at')->get()),
        ]);
    }

    public function store(StoreExpenseRequest $request, Occasion $occasion, RecordExpenseService $service): JsonResponse
    {
        $expense = $service->handle($occasion, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new ExpenseResource($expense->load('category')),
        ], 201);
    }
}
