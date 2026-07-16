<?php

namespace Database\Factories;

use App\Domains\Finance\Domain\Models\BudgetCategory;
use App\Domains\Finance\Domain\Models\BudgetItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BudgetItem>
 */
class BudgetItemFactory extends Factory
{
    protected $model = BudgetItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'budget_category_id' => BudgetCategory::factory(),
            'name' => $this->faker->words(2, true),
            'estimated_cost' => $this->faker->randomFloat(2, 1000, 100000),
            'currency' => 'TZS',
            'created_by' => User::factory(),
        ];
    }
}
