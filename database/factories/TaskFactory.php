<?php

namespace Database\Factories;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Enums\TaskPriority;
use App\Domains\Planning\Domain\Enums\TaskStatus;
use App\Domains\Planning\Domain\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'occasion_id' => Occasion::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(),
            'status' => TaskStatus::Open,
            'priority' => TaskPriority::Medium,
            'assignee_id' => null,
            'created_by' => User::factory(),
        ];
    }
}
