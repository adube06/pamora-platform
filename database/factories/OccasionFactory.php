<?php

namespace Database\Factories;

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Enums\OccasionType;
use App\Domains\Occasion\Domain\Enums\OccasionVisibility;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Occasion>
 */
class OccasionFactory extends Factory
{
    protected $model = Occasion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->words(3, true);
        $host = User::factory();

        return [
            'host_id' => $host,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 999999),
            'title' => $title,
            'type' => $this->faker->randomElement(OccasionType::cases()),
            'description' => $this->faker->sentence(),
            'primary_date' => $this->faker->dateTimeBetween('+1 week', '+6 months')->format('Y-m-d'),
            'timezone' => 'Africa/Dar_es_Salaam',
            'location' => $this->faker->city(),
            'visibility' => OccasionVisibility::Private,
            'status' => OccasionStatus::Draft,
            'created_by' => $host,
        ];
    }
}
