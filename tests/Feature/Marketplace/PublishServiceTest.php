<?php

use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;

it('lets an approved vendor publish a fixed-price service', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id, 'verification_status' => VendorVerificationStatus::Verified]);

    $this->actingAs($owner)
        ->post("/vendor/{$vendor->uuid}/services", [
            'category' => 'photography',
            'name' => 'Wedding Photography Package',
            'description' => 'Full day coverage',
            'pricing_model' => 'fixed',
            'price' => 500000,
            'estimated_duration' => 'Full day',
        ])
        ->assertSessionHasNoErrors();

    $service = Service::firstWhere('name', 'Wedding Photography Package');

    expect($service)->not->toBeNull()
        ->and($service->vendor_id)->toBe($vendor->id)
        ->and($service->pricing_model->value)->toBe('fixed')
        ->and((float) $service->price)->toBe(500000.0)
        ->and($service->currency)->toBe('TZS');
});

it('lets an approved vendor publish a custom-pricing service', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id, 'verification_status' => VendorVerificationStatus::Verified]);

    $this->actingAs($owner)
        ->post("/vendor/{$vendor->uuid}/services", [
            'category' => 'catering',
            'name' => 'Custom Catering',
            'pricing_model' => 'custom',
        ])
        ->assertSessionHasNoErrors();

    $service = Service::firstWhere('name', 'Custom Catering');

    expect($service)->not->toBeNull()
        ->and($service->pricing_model->value)->toBe('custom')
        ->and($service->price)->toBeNull();
});

it('rejects publishing a service from an unapproved vendor', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id, 'verification_status' => VendorVerificationStatus::Pending]);

    $this->actingAs($owner)
        ->post("/vendor/{$vendor->uuid}/services", [
            'category' => 'photography',
            'name' => 'Should not publish',
            'pricing_model' => 'custom',
        ])
        ->assertSessionHasErrors('name');

    expect(Service::where('vendor_id', $vendor->id)->exists())->toBeFalse();
});

it('rejects a fixed-price service without a price', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id, 'verification_status' => VendorVerificationStatus::Verified]);

    $this->actingAs($owner)
        ->post("/vendor/{$vendor->uuid}/services", [
            'category' => 'photography',
            'name' => 'Should not publish',
            'pricing_model' => 'fixed',
        ])
        ->assertSessionHasErrors('price');

    expect(Service::where('vendor_id', $vendor->id)->exists())->toBeFalse();
});

it('prevents a non-owner from publishing a service for another vendor', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id, 'verification_status' => VendorVerificationStatus::Verified]);
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->post("/vendor/{$vendor->uuid}/services", [
            'category' => 'photography',
            'name' => 'Should not publish',
            'pricing_model' => 'custom',
        ])
        ->assertForbidden();

    expect(Service::where('vendor_id', $vendor->id)->exists())->toBeFalse();
});
