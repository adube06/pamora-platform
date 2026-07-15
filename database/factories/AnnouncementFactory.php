<?php

namespace Database\Factories;

use App\Domains\Communication\Domain\Models\Announcement;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

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
            'message' => $this->faker->paragraph(),
            'audience' => 'all_members',
            'status' => 'published',
            'published_at' => now(),
            'created_by' => User::factory(),
        ];
    }
}
