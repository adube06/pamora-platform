<?php

namespace Database\Factories;

use App\Domains\Finance\Domain\Models\Budget;
use App\Domains\Finance\Domain\Models\BudgetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BudgetCategory>
 */
class BudgetCategoryFactory extends Factory
{
    protected $model = BudgetCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'budget_id' => Budget::factory(),
            'name' => $this->faker->words(2, true),
        ];
    }
}
