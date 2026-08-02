<?php

namespace App\Domains\Finance\Presentation\Http\Controllers\Api;

use App\Domains\Finance\Application\Services\GetBudgetSummaryService;
use App\Domains\Finance\Presentation\Http\Resources\BudgetResource;
use App\Domains\Finance\Presentation\Http\Resources\ContributionResource;
use App\Domains\Finance\Presentation\Http\Resources\ExpenseResource;
use App\Domains\Finance\Presentation\Http\Resources\PledgeResource;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class FinanceController
{
    public function index(Request $request, Occasion $occasion, GetBudgetSummaryService $summaryService): JsonResponse
    {
        Gate::authorize('view', $occasion);

        // Budget/Expense figures are permission-gated (finance.view_budget);
        // Contribution/Pledge figures are transparency-open to any member —
        // same split the web FinanceController applies. The API must not
        // leak them just because a UI would hide them.
        $canViewBudget = $request->user()->can('view-budget', $occasion);
        $summary = $summaryService->handle($occasion);

        return ApiResponse::success([
            'budget' => $canViewBudget && $occasion->budget
                ? new BudgetResource($occasion->budget->load('categories.budgetItems'))
                : null,
            'contributions' => ContributionResource::collection(
                $occasion->contributions()->latest('contributed_at')->get()
            ),
            'pledges' => PledgeResource::collection($occasion->pledges()->latest('pledged_at')->get()),
            'expenses' => $canViewBudget
                ? ExpenseResource::collection($occasion->expenses()->with('category')->latest('spent_at')->get())
                : [],
            'summary' => $canViewBudget
                ? $summary
                : Arr::only($summary, ['total_received', 'contribution_count', 'total_pledged', 'pending_pledged']),
            'permissions' => [
                'can_record_contribution' => $request->user()->can('record-contribution', $occasion),
                'can_record_pledge' => $request->user()->can('record-pledge', $occasion),
                'can_view_budget' => $canViewBudget,
                'can_edit_budget' => $request->user()->can('edit-budget', $occasion),
                'can_record_expense' => $request->user()->can('record-expense', $occasion),
            ],
        ]);
    }
}
