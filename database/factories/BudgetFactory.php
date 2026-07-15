<?php

namespace Database\Factories;

use App\Domains\Finance\Domain\Enums\BudgetStatus;
use App\Domains\Finance\Domain\Models\Budget;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'occasion_id' => Occasion::factory(),
            'name' => $this->faker->words(2, true).' Budget',
            'currency' => 'TZS',
            'planned_amount' => $this->faker->randomFloat(2, 500000, 5000000),
            'status' => BudgetStatus::Active,
            'created_by' => User::factory(),
        ];
    }
}
