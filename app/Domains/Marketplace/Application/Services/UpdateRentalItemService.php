<?php

namespace App\Domains\Marketplace\Application\Services;

use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Events\RentalItemUpdated;
use App\Domains\Marketplace\Domain\Models\RentalItem;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateRentalItemService
{
    /**
     * @param  array{name: string, description?: string|null, quantity_available: int, unit_price: string|float}  $data
     */
    public function handle(RentalItem $rentalItem, array $data, User $actor): RentalItem
    {
        if ($rentalItem->vendor->verification_status !== VendorVerificationStatus::Verified) {
            throw ValidationException::withMessages([
                'name' => 'Only an approved Vendor may edit Rental Items.',
            ]);
        }

        $rentalItem->update($data);

        RentalItemUpdated::dispatch($rentalItem->fresh(), $actor);

        return $rentalItem;
    }
}
