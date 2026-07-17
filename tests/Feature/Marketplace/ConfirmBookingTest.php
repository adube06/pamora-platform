<?php

use App\Domains\Marketplace\Domain\Enums\BookingStatus;
use App\Domains\Marketplace\Domain\Enums\QuotationStatus;
use App\Domains\Marketplace\Domain\Models\AvailabilityBlock;
use App\Domains\Marketplace\Domain\Models\Booking;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets a host confirm an accepted quotation as a booking', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $vendor = Vendor::factory()->create();
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $quotation = Quotation::factory()->create([
        'occasion_id' => $occasion->id,
        'service_id' => $service->id,
        'requested_by' => $host->id,
        'status' => QuotationStatus::Accepted,
        'quoted_price' => 150000,
        'currency' => 'TZS',
    ]);

    $this->actingAs($host)
        ->patch("/quotations/{$quotation->uuid}/confirm")
        ->assertSessionHasNoErrors();

    $booking = Booking::where('quotation_id', $quotation->id)->first();

    expect($booking)->not->toBeNull()
        ->and($booking->status)->toBe(BookingStatus::Confirmed)
        ->and($booking->occasion_id)->toBe($occasion->id)
        ->and($booking->service_id)->toBe($service->id)
        ->and((float) $booking->agreed_price)->toBe(150000.0)
        ->and($booking->currency)->toBe('TZS')
        ->and($booking->confirmed_by)->toBe($host->id);
});

it('rejects confirming a quotation that is not accepted', function () {
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
        ->patch("/quotations/{$quotation->uuid}/confirm")
        ->assertSessionHasErrors('status');

    expect(Booking::where('quotation_id', $quotation->id)->exists())->toBeFalse();
});

it('rejects confirming the same quotation twice', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $vendor = Vendor::factory()->create();
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $quotation = Quotation::factory()->create([
        'occasion_id' => $occasion->id,
        'service_id' => $service->id,
        'requested_by' => $host->id,
        'status' => QuotationStatus::Accepted,
        'quoted_price' => 100000,
    ]);

    $this->actingAs($host)->patch("/quotations/{$quotation->uuid}/confirm")->assertSessionHasNoErrors();

    $this->actingAs($host)
        ->patch("/quotations/{$quotation->uuid}/confirm")
        ->assertSessionHasErrors('status');

    expect(Booking::where('quotation_id', $quotation->id)->count())->toBe(1);
});

it('prevents a member without marketplace.confirm_booking from confirming a booking', function () {
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
        'status' => QuotationStatus::Accepted,
    ]);

    $this->actingAs($guestUser)
        ->patch("/quotations/{$quotation->uuid}/confirm")
        ->assertForbidden();

    expect(Booking::where('quotation_id', $quotation->id)->exists())->toBeFalse();
});

it('rejects confirming a booking when the vendor has an availability block covering the occasion date', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'primary_date' => '2026-08-02']);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $vendor = Vendor::factory()->create();
    AvailabilityBlock::factory()->create([
        'vendor_id' => $vendor->id,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-03',
    ]);
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $quotation = Quotation::factory()->create([
        'occasion_id' => $occasion->id,
        'service_id' => $service->id,
        'requested_by' => $host->id,
        'status' => QuotationStatus::Accepted,
    ]);

    $this->actingAs($host)
        ->patch("/quotations/{$quotation->uuid}/confirm")
        ->assertSessionHasErrors('status');

    expect(Booking::where('quotation_id', $quotation->id)->exists())->toBeFalse();
});

it('confirms a booking when the occasion has no primary date set', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id, 'primary_date' => null]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $vendor = Vendor::factory()->create();
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $quotation = Quotation::factory()->create([
        'occasion_id' => $occasion->id,
        'service_id' => $service->id,
        'requested_by' => $host->id,
        'status' => QuotationStatus::Accepted,
        'quoted_price' => 100000,
    ]);

    $this->actingAs($host)
        ->patch("/quotations/{$quotation->uuid}/confirm")
        ->assertSessionHasNoErrors();

    expect(Booking::where('quotation_id', $quotation->id)->exists())->toBeTrue();
});
