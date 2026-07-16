<?php

namespace Database\Factories;

use App\Domains\Media\Domain\Models\Album;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Album>
 */
class AlbumFactory extends Factory
{
    protected $model = Album::class;

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
            'created_by' => User::factory(),
        ];
    }
}
