<?php

namespace Database\Factories;

use App\Domains\Marketplace\Domain\Enums\BookingStatus;
use App\Domains\Marketplace\Domain\Models\Booking;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'occasion_id' => Occasion::factory(),
            'service_id' => Service::factory(),
            'quotation_id' => Quotation::factory(),
            'confirmed_by' => User::factory(),
            'status' => BookingStatus::Confirmed,
            'agreed_price' => $this->faker->randomFloat(2, 50000, 500000),
            'currency' => 'TZS',
            'confirmed_at' => now(),
        ];
    }
}
