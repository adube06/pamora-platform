<?php

use App\Domains\Marketplace\Domain\Enums\BookingStatus;
use App\Domains\Marketplace\Domain\Models\Booking;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets the owning vendor complete a confirmed booking', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $vendorOwner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $vendorOwner->id]);
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $quotation = Quotation::factory()->create([
        'occasion_id' => $occasion->id,
        'service_id' => $service->id,
        'requested_by' => $host->id,
    ]);
    $booking = Booking::factory()->create([
        'occasion_id' => $occasion->id,
        'service_id' => $service->id,
        'quotation_id' => $quotation->id,
        'confirmed_by' => $host->id,
        'status' => BookingStatus::Confirmed,
    ]);

    $this->actingAs($vendorOwner)
        ->patch("/bookings/{$booking->uuid}/complete")
        ->assertSessionHasNoErrors();

    expect($booking->fresh()->status)->toBe(BookingStatus::Completed);
});

it('rejects completing a booking that is not confirmed', function () {
    $vendorOwner = User::factory()->create();
    $vendor = Vendor::factory()->create(['owner_id' => $vendorOwner->id]);
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $quotation = Quotation::factory()->create(['service_id' => $service->id]);
    $booking = Booking::factory()->create([
        'service_id' => $service->id,
        'quotation_id' => $quotation->id,
        'status' => BookingStatus::Completed,
    ]);

    $this->actingAs($vendorOwner)
        ->patch("/bookings/{$booking->uuid}/complete")
        ->assertSessionHasErrors('status');

    expect($booking->fresh()->status)->toBe(BookingStatus::Completed);
});

it('prevents a user who is not the owning vendor from completing a booking', function () {
    $otherUser = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $quotation = Quotation::factory()->create(['service_id' => $service->id]);
    $booking = Booking::factory()->create([
        'service_id' => $service->id,
        'quotation_id' => $quotation->id,
        'status' => BookingStatus::Confirmed,
    ]);

    $this->actingAs($otherUser)
        ->patch("/bookings/{$booking->uuid}/complete")
        ->assertForbidden();

    expect($booking->fresh()->status)->toBe(BookingStatus::Confirmed);
});
