<?php

namespace App\Domains\Finance\Presentation\Http\Controllers\Api;

use App\Domains\Finance\Application\Services\RecordExpenseService;
use App\Domains\Finance\Presentation\Http\Requests\StoreExpenseRequest;
use App\Domains\Finance\Presentation\Http\Resources\ExpenseResource;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExpenseController
{
    public function index(Request $request, Occasion $occasion): JsonResponse
    {
        Gate::authorize('view-budget', $occasion);

        return ApiResponse::success(
            ExpenseResource::collection($occasion->expenses()->with('category')->latest('spent_at')->get())
        );
    }

    public function store(StoreExpenseRequest $request, Occasion $occasion, RecordExpenseService $service): JsonResponse
    {
        $expense = $service->handle($occasion, $request->validated(), $request->user());

        return ApiResponse::success(new ExpenseResource($expense->load('category')), 201);
    }
}
