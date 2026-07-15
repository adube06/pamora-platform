<?php

namespace App\Domains\Finance\Presentation\Http\Controllers;

use App\Domains\Finance\Application\Services\RecordExpenseService;
use App\Domains\Finance\Presentation\Http\Requests\StoreExpenseRequest;
use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\RedirectResponse;

class ExpenseController
{
    public function store(StoreExpenseRequest $request, Occasion $occasion, RecordExpenseService $service): RedirectResponse
    {
        $service->handle($occasion, $request->validated(), $request->user());

        return back()->with('success', 'Expense recorded.');
    }
}
