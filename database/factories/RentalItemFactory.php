<?php

namespace Database\Factories;

use App\Domains\Marketplace\Domain\Enums\RentalItemStatus;
use App\Domains\Marketplace\Domain\Models\RentalItem;
use App\Domains\Marketplace\Domain\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentalItem>
 */
class RentalItemFactory extends Factory
{
    protected $model = RentalItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::factory(),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'quantity_available' => $this->faker->numberBetween(1, 100),
            'unit_price' => $this->faker->numberBetween(5000, 100000),
            'currency' => 'TZS',
            'status' => RentalItemStatus::Active,
        ];
    }
}
