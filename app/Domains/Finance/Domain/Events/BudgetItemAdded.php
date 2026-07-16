<?php

namespace App\Domains\Finance\Domain\Events;

use App\Domains\Finance\Domain\Models\BudgetItem;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BudgetItemAdded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly BudgetItem $budgetItem,
        public readonly User $actor,
    ) {}
}
