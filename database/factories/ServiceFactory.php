<?php

namespace Database\Factories;

use App\Domains\Marketplace\Domain\Enums\PricingModel;
use App\Domains\Marketplace\Domain\Enums\ServiceStatus;
use App\Domains\Marketplace\Domain\Enums\VendorCategory;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Domain\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::factory(),
            'category' => VendorCategory::Photography,
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'pricing_model' => PricingModel::Fixed,
            'price' => $this->faker->numberBetween(50000, 2000000),
            'currency' => 'TZS',
            'estimated_duration' => '2 hours',
            'status' => ServiceStatus::Active,
        ];
    }
}
