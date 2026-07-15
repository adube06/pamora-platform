<?php

namespace Database\Factories;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimelineEvent>
 */
class TimelineEventFactory extends Factory
{
    protected $model = TimelineEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'occasion_id' => Occasion::factory(),
            'name' => $this->faker->words(2, true),
            'scheduled_at' => now()->addDays($this->faker->numberBetween(1, 30)),
            'created_by' => User::factory(),
        ];
    }
}
