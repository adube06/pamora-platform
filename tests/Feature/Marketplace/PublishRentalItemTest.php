<?php

use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Models\RentalItem;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;

it('lets an approved vendor publish a rental item', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id, 'verification_status' => VendorVerificationStatus::Verified]);

    $this->actingAs($owner)
        ->post("/vendor/{$vendor->uuid}/rental-items", [
            'name' => 'White Chiavari Chairs',
            'description' => 'Set of 100',
            'quantity_available' => 100,
            'unit_price' => 5000,
        ])
        ->assertSessionHasNoErrors();

    $rentalItem = RentalItem::firstWhere('name', 'White Chiavari Chairs');

    expect($rentalItem)->not->toBeNull()
        ->and($rentalItem->vendor_id)->toBe($vendor->id)
        ->and($rentalItem->quantity_available)->toBe(100)
        ->and((float) $rentalItem->unit_price)->toBe(5000.0)
        ->and($rentalItem->currency)->toBe('TZS')
        ->and($rentalItem->status->value)->toBe('active');
});

it('rejects publishing a rental item from an unapproved vendor', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id, 'verification_status' => VendorVerificationStatus::Pending]);

    $this->actingAs($owner)
        ->post("/vendor/{$vendor->uuid}/rental-items", [
            'name' => 'Should not publish',
            'quantity_available' => 10,
            'unit_price' => 1000,
        ])
        ->assertSessionHasErrors('name');

    expect(RentalItem::where('vendor_id', $vendor->id)->exists())->toBeFalse();
});

it('prevents a non-owner from publishing a rental item for another vendor', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id, 'verification_status' => VendorVerificationStatus::Verified]);
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->post("/vendor/{$vendor->uuid}/rental-items", [
            'name' => 'Should not publish',
            'quantity_available' => 10,
            'unit_price' => 1000,
        ])
        ->assertForbidden();

    expect(RentalItem::where('vendor_id', $vendor->id)->exists())->toBeFalse();
});
