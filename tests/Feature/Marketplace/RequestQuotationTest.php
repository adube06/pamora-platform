<?php

use App\Domains\Marketplace\Domain\Enums\QuotationStatus;
use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets a host request a quotation from an active service', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $vendor = Vendor::factory()->create(['verification_status' => VendorVerificationStatus::Verified]);
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/quotations", [
            'service_id' => $service->id,
            'message' => 'Looking for photography for our wedding.',
        ])
        ->assertSessionHasNoErrors();

    $quotation = Quotation::firstWhere('service_id', $service->id);

    expect($quotation)->not->toBeNull()
        ->and($quotation->occasion_id)->toBe($occasion->id)
        ->and($quotation->requested_by)->toBe($host->id)
        ->and($quotation->status)->toBe(QuotationStatus::Draft);
});

it('rejects requesting a quotation from an unverified vendor\'s service', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $vendor = Vendor::factory()->create(['verification_status' => VendorVerificationStatus::Pending]);
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/quotations", ['service_id' => $service->id])
        ->assertSessionHasErrors('service_id');

    expect(Quotation::where('service_id', $service->id)->exists())->toBeFalse();
});

it('rejects requesting a quotation on an archived occasion (BR-009)', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'status' => OccasionStatus::Archived]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $vendor = Vendor::factory()->create(['verification_status' => VendorVerificationStatus::Verified]);
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);

    $this->actingAs($host)
        ->post("/occasions/{$occasion->slug}/quotations", ['service_id' => $service->id])
        ->assertSessionHasErrors('occasion');

    expect(Quotation::where('service_id', $service->id)->exists())->toBeFalse();
});

it('prevents a member without marketplace.request_quotation from requesting a quotation', function () {
    $occasion = Occasion::factory()->create();
    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $vendor = Vendor::factory()->create(['verification_status' => VendorVerificationStatus::Verified]);
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);

    $this->actingAs($guestUser)
        ->post("/occasions/{$occasion->slug}/quotations", ['service_id' => $service->id])
        ->assertForbidden();

    expect(Quotation::where('service_id', $service->id)->exists())->toBeFalse();
});
