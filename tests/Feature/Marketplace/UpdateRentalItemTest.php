<?php

use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Models\RentalItem;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;

it('lets the owning vendor update a rental item', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id, 'verification_status' => VendorVerificationStatus::Verified]);
    $rentalItem = RentalItem::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Old Name', 'quantity_available' => 10]);

    $this->actingAs($owner)
        ->patch("/vendor/rental-items/{$rentalItem->uuid}", [
            'name' => 'New Name',
            'quantity_available' => 25,
            'unit_price' => 2000,
        ])
        ->assertSessionHasNoErrors();

    $rentalItem->refresh();

    expect($rentalItem->name)->toBe('New Name')
        ->and($rentalItem->quantity_available)->toBe(25);
});

it('prevents a non-owner from updating a rental item', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id, 'verification_status' => VendorVerificationStatus::Verified]);
    $rentalItem = RentalItem::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Old Name']);
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->patch("/vendor/rental-items/{$rentalItem->uuid}", [
            'name' => 'Should not update',
            'quantity_available' => 5,
            'unit_price' => 1000,
        ])
        ->assertForbidden();

    expect($rentalItem->fresh()->name)->toBe('Old Name');
});
