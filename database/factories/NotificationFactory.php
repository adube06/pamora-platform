<?php

namespace Database\Factories;

use App\Domains\Communication\Domain\Models\Notification;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'occasion_id' => Occasion::factory(),
            'subject_type' => 'Task',
            'subject_id' => Task::factory(),
            'type' => 'task_assigned',
            'title' => $this->faker->sentence(3),
            'body' => $this->faker->sentence(),
            'read_at' => null,
        ];
    }
}
