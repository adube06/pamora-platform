<?php

use App\Domains\Marketplace\Domain\Enums\QuotationStatus;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets a host accept a submitted quotation', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $vendor = Vendor::factory()->create();
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $quotation = Quotation::factory()->create([
        'occasion_id' => $occasion->id,
        'service_id' => $service->id,
        'requested_by' => $host->id,
        'status' => QuotationStatus::Submitted,
    ]);

    $this->actingAs($host)
        ->patch("/quotations/{$quotation->uuid}/accept")
        ->assertSessionHasNoErrors();

    expect($quotation->fresh()->status)->toBe(QuotationStatus::Accepted);
});

it('rejects accepting a quotation that is not submitted', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $vendor = Vendor::factory()->create();
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $quotation = Quotation::factory()->create([
        'occasion_id' => $occasion->id,
        'service_id' => $service->id,
        'requested_by' => $host->id,
        'status' => QuotationStatus::Draft,
    ]);

    $this->actingAs($host)
        ->patch("/quotations/{$quotation->uuid}/accept")
        ->assertSessionHasErrors('status');

    expect($quotation->fresh()->status)->toBe(QuotationStatus::Draft);
});

it('prevents a member without marketplace.request_quotation from accepting a quotation', function () {
    $occasion = Occasion::factory()->create();
    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $vendor = Vendor::factory()->create();
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $quotation = Quotation::factory()->create([
        'occasion_id' => $occasion->id,
        'service_id' => $service->id,
        'status' => QuotationStatus::Submitted,
    ]);

    $this->actingAs($guestUser)
        ->patch("/quotations/{$quotation->uuid}/accept")
        ->assertForbidden();

    expect($quotation->fresh()->status)->toBe(QuotationStatus::Submitted);
});
