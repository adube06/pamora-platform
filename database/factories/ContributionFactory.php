<?php

namespace Database\Factories;

use App\Domains\Finance\Domain\Enums\ContributionMethod;
use App\Domains\Finance\Domain\Models\Contribution;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contribution>
 */
class ContributionFactory extends Factory
{
    protected $model = Contribution::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'occasion_id' => Occasion::factory(),
            'contributor_name' => $this->faker->name(),
            'contributor_phone' => $this->faker->optional()->phoneNumber(),
            'amount' => $this->faker->randomFloat(2, 1000, 100000),
            'currency' => 'TZS',
            'method' => ContributionMethod::Cash,
            'message' => $this->faker->optional()->sentence(),
            'recorded_by' => User::factory(),
            'contributed_at' => now()->toDateString(),
        ];
    }
}
