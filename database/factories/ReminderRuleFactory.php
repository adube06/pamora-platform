<?php

namespace Database\Factories;

use App\Domains\Communication\Domain\Models\ReminderRule;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReminderRule>
 */
class ReminderRuleFactory extends Factory
{
    protected $model = ReminderRule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'occasion_id' => Occasion::factory(),
            'timeline_event_id' => TimelineEvent::factory(),
            'offset_minutes' => 120,
            'created_by' => User::factory(),
            'triggered_at' => null,
        ];
    }
}
