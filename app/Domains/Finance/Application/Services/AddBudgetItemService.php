<?php

namespace App\Domains\Finance\Application\Services;

use App\Domains\Finance\Domain\Events\BudgetItemAdded;
use App\Domains\Finance\Domain\Models\BudgetItem;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;

class AddBudgetItemService
{
    use GuardsAgainstArchivedOccasion;

    /**
     * @param  array{budget_category_id: int, name: string, estimated_cost: string|float}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): BudgetItem
    {
        $this->ensureOccasionAcceptsActivity($occasion);

        $budgetItem = BudgetItem::create([
            ...$data,
            'created_by' => $actor->id,
            'currency' => $data['currency'] ?? 'TZS',
        ]);

        BudgetItemAdded::dispatch($budgetItem, $actor);

        return $budgetItem;
    }
}
