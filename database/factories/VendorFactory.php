<?php

namespace Database\Factories;

use App\Domains\Marketplace\Domain\Enums\VendorCategory;
use App\Domains\Marketplace\Domain\Enums\VendorStatus;
use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'business_name' => $this->faker->company(),
            'categories' => [VendorCategory::Photography->value],
            'service_areas' => [$this->faker->city()],
            'contact_email' => $this->faker->companyEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'verification_status' => VendorVerificationStatus::Pending,
            'status' => VendorStatus::Active,
        ];
    }
}
