<?php

namespace Database\Factories;

use App\Domains\Marketplace\Domain\Models\AvailabilityBlock;
use App\Domains\Marketplace\Domain\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AvailabilityBlock>
 */
class AvailabilityBlockFactory extends Factory
{
    protected $model = AvailabilityBlock::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 week', '+2 weeks');

        return [
            'vendor_id' => Vendor::factory(),
            'start_date' => $start,
            'end_date' => $start,
            'reason' => $this->faker->sentence(),
        ];
    }
}
