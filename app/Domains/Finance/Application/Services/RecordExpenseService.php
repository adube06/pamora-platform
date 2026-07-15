<?php

namespace App\Domains\Finance\Application\Services;

use App\Domains\Finance\Domain\Events\ExpenseRecorded;
use App\Domains\Finance\Domain\Models\Expense;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;

class RecordExpenseService
{
    /**
     * @param  array{budget_category_id: int, amount: string|float, description?: string, spent_at: string}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): Expense
    {
        $expense = Expense::create([
            ...$data,
            'occasion_id' => $occasion->id,
            'recorded_by' => $actor->id,
            'currency' => $data['currency'] ?? 'TZS',
        ]);

        ExpenseRecorded::dispatch($expense, $actor);

        return $expense;
    }
}
