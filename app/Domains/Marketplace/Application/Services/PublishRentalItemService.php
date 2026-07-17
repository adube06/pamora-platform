<?php

namespace App\Domains\Marketplace\Application\Services;

use App\Domains\Marketplace\Domain\Enums\RentalItemStatus;
use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Events\RentalItemPublished;
use App\Domains\Marketplace\Domain\Models\RentalItem;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PublishRentalItemService
{
    /**
     * @param  array{name: string, description?: string|null, quantity_available: int, unit_price: string|float}  $data
     */
    public function handle(Vendor $vendor, array $data, User $actor): RentalItem
    {
        if ($vendor->verification_status !== VendorVerificationStatus::Verified) {
            throw ValidationException::withMessages([
                'name' => 'Only an approved Vendor may publish Rental Items.',
            ]);
        }

        $rentalItem = $vendor->rentalItems()->create([
            ...$data,
            'currency' => 'TZS',
            'status' => RentalItemStatus::Active,
        ]);

        RentalItemPublished::dispatch($rentalItem, $actor);

        return $rentalItem;
    }
}
