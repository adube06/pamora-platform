<?php

namespace Database\Factories;

use App\Domains\Finance\Domain\Enums\PledgeStatus;
use App\Domains\Finance\Domain\Models\Pledge;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pledge>
 */
class PledgeFactory extends Factory
{
    protected $model = Pledge::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'occasion_id' => Occasion::factory(),
            'pledgor_name' => $this->faker->name(),
            'pledgor_phone' => $this->faker->optional()->phoneNumber(),
            'amount' => $this->faker->randomFloat(2, 1000, 100000),
            'currency' => 'TZS',
            'status' => PledgeStatus::Pending,
            'message' => $this->faker->optional()->sentence(),
            'recorded_by' => User::factory(),
            'pledged_at' => now()->toDateString(),
        ];
    }
}
