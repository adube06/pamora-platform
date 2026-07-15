<?php

namespace App\Domains\Finance\Presentation\Http\Controllers;

use App\Domains\Finance\Application\Services\CreateBudgetService;
use App\Domains\Finance\Presentation\Http\Requests\StoreBudgetRequest;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\RedirectResponse;

class BudgetController
{
    public function store(StoreBudgetRequest $request, Occasion $occasion, CreateBudgetService $service): RedirectResponse
    {
        $service->handle($occasion, $request->validated(), $request->user());

        return back()->with('success', 'Budget created.');
    }
}
