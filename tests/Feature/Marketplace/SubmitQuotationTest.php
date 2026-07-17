<?php

use App\Domains\Marketplace\Domain\Enums\QuotationStatus;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;

it('lets the owning vendor submit a quotation', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id]);
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $quotation = Quotation::factory()->create(['service_id' => $service->id, 'status' => QuotationStatus::Draft]);

    $this->actingAs($owner)
        ->patch("/quotations/{$quotation->uuid}/submit", [
            'quoted_price' => 750000,
            'vendor_notes' => 'Includes travel within the city.',
        ])
        ->assertSessionHasNoErrors();

    $quotation->refresh();
    expect($quotation->status)->toBe(QuotationStatus::Submitted)
        ->and((float) $quotation->quoted_price)->toBe(750000.0)
        ->and($quotation->responded_at)->not->toBeNull();
});

it('rejects submitting a quotation that is not in draft status', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id]);
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $quotation = Quotation::factory()->create(['service_id' => $service->id, 'status' => QuotationStatus::Submitted]);

    $this->actingAs($owner)
        ->patch("/quotations/{$quotation->uuid}/submit", ['quoted_price' => 100000])
        ->assertSessionHasErrors('quoted_price');
});

it('prevents a non-owner from submitting a quotation', function () {
    $owner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $owner->id]);
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $quotation = Quotation::factory()->create(['service_id' => $service->id, 'status' => QuotationStatus::Draft]);
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->patch("/quotations/{$quotation->uuid}/submit", ['quoted_price' => 100000])
        ->assertForbidden();

    expect($quotation->fresh()->status)->toBe(QuotationStatus::Draft);
});
