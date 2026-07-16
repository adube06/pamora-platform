<?php

namespace App\Domains\Finance\Application\Services;

use App\Domains\Finance\Domain\Enums\BudgetStatus;
use App\Domains\Finance\Domain\Events\BudgetCreated;
use App\Domains\Finance\Domain\Models\Budget;
use App\Domains\Finance\Domain\Models\BudgetCategory;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateBudgetService
{
    use GuardsAgainstArchivedOccasion;

    /**
     * Default Budget Categories seeded for every new Budget, per the
     * Finance PRD's Budget Category examples. Category CRUD is out of
     * scope for this slice (Design Decision 2) — these are fixed.
     *
     * @var list<string>
     */
    private const DEFAULT_CATEGORIES = [
        'Venue',
        'Catering',
        'Decoration',
        'Transport',
        'Photography',
        'Entertainment',
        'Miscellaneous',
    ];

    /**
     * @param  array{name: string, planned_amount: string|float, currency?: string}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): Budget
    {
        $this->ensureOccasionAcceptsActivity($occasion);

        return DB::transaction(function () use ($occasion, $data, $actor) {
            $budget = Budget::create([
                'occasion_id' => $occasion->id,
                'name' => $data['name'],
                'currency' => $data['currency'] ?? 'TZS',
                'planned_amount' => $data['planned_amount'],
                'status' => BudgetStatus::Active,
                'created_by' => $actor->id,
            ]);

            foreach (self::DEFAULT_CATEGORIES as $name) {
                BudgetCategory::create([
                    'budget_id' => $budget->id,
                    'name' => $name,
                ]);
            }

            BudgetCreated::dispatch($budget, $actor);

            return $budget;
        });
    }
}
