<?php

namespace Database\Factories;

use App\Domains\Finance\Domain\Models\BudgetCategory;
use App\Domains\Finance\Domain\Models\Expense;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'occasion_id' => Occasion::factory(),
            'budget_category_id' => BudgetCategory::factory(),
            'amount' => $this->faker->randomFloat(2, 1000, 100000),
            'currency' => 'TZS',
            'description' => $this->faker->optional()->sentence(),
            'spent_at' => now()->toDateString(),
            'recorded_by' => User::factory(),
        ];
    }
}
