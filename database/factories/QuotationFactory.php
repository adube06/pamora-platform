<?php

namespace Database\Factories;

use App\Domains\Marketplace\Domain\Enums\QuotationStatus;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quotation>
 */
class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

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
            'requested_by' => User::factory(),
            'message' => $this->faker->sentence(),
            'status' => QuotationStatus::Draft,
            'currency' => 'TZS',
            'requested_at' => now(),
        ];
    }
}
