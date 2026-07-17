<?php

use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;

it('lets the owning vendor update a service while active', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id, 'verification_status' => VendorVerificationStatus::Verified]);
    $service = Service::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Old Name']);

    $this->actingAs($owner)
        ->patch("/vendor/services/{$service->uuid}", [
            'category' => $service->category->value,
            'name' => 'New Name',
            'pricing_model' => 'custom',
        ])
        ->assertSessionHasNoErrors();

    expect($service->fresh()->name)->toBe('New Name');
});

it('prevents a non-owner from updating a service', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id, 'verification_status' => VendorVerificationStatus::Verified]);
    $service = Service::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Old Name']);
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->patch("/vendor/services/{$service->uuid}", [
            'category' => $service->category->value,
            'name' => 'Should not update',
            'pricing_model' => 'custom',
        ])
        ->assertForbidden();

    expect($service->fresh()->name)->toBe('Old Name');
});
