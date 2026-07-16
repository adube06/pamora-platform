<?php

namespace App\Domains\Finance\Presentation\Http\Controllers;

use App\Domains\Finance\Application\Services\AddBudgetItemService;
use App\Domains\Finance\Presentation\Http\Requests\StoreBudgetItemRequest;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\RedirectResponse;

class BudgetItemController
{
    public function store(StoreBudgetItemRequest $request, Occasion $occasion, AddBudgetItemService $service): RedirectResponse
    {
        $service->handle($occasion, $request->validated(), $request->user());

        return back()->with('success', 'Budget item added.');
    }
}
