<?php

use App\Domains\Insights\Application\Services\GetVendorReputationService;
use App\Domains\Marketplace\Domain\Enums\BookingStatus;
use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Models\Booking;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Domains\Marketplace\Domain\Models\Review;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Domain\Models\Vendor;

it('returns only baseline signals for a brand-new vendor with no reviews or bookings', function () {
    $vendor = Vendor::factory()->create(['verification_status' => VendorVerificationStatus::Verified, 'service_areas' => null]);

    $reputation = app(GetVendorReputationService::class)->handle($vendor);

    $keys = array_column($reputation['signals'], 'key');

    expect($keys)->toContain('verification_status')
        ->and($keys)->toContain('profile_completeness')
        ->and($keys)->not->toContain('review_rating')
        ->and($keys)->not->toContain('booking_completion_rate')
        ->and($reputation['review_count'])->toBe(0)
        ->and($reputation['average_response_hours'])->toBeNull();
});

it('includes a review rating signal once the vendor has reviews', function () {
    $vendor = Vendor::factory()->create(['verification_status' => VendorVerificationStatus::Verified]);
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $booking = Booking::factory()->create(['service_id' => $service->id, 'status' => BookingStatus::Completed]);

    Review::factory()->create(['service_id' => $service->id, 'booking_id' => $booking->id, 'rating' => 5]);

    $reputation = app(GetVendorReputationService::class)->handle($vendor);

    $reviewSignal = collect($reputation['signals'])->firstWhere('key', 'review_rating');

    expect($reviewSignal)->not->toBeNull()
        ->and($reviewSignal['value'])->toBe(100)
        ->and($reputation['review_count'])->toBe(1);
});

it('includes a booking completion rate signal once the vendor has bookings', function () {
    $vendor = Vendor::factory()->create(['verification_status' => VendorVerificationStatus::Verified]);
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);

    Booking::factory()->create(['service_id' => $service->id, 'status' => BookingStatus::Completed]);
    Booking::factory()->create(['service_id' => $service->id, 'status' => BookingStatus::Confirmed]);

    $reputation = app(GetVendorReputationService::class)->handle($vendor);

    $completionSignal = collect($reputation['signals'])->firstWhere('key', 'booking_completion_rate');

    expect($completionSignal)->not->toBeNull()
        ->and($completionSignal['value'])->toBe(50);
});

it('computes average response hours from submitted quotations', function () {
    $vendor = Vendor::factory()->create(['verification_status' => VendorVerificationStatus::Verified]);
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);

    Quotation::factory()->create([
        'service_id' => $service->id,
        'requested_at' => now()->subHours(10),
        'responded_at' => now(),
    ]);

    $reputation = app(GetVendorReputationService::class)->handle($vendor);

    expect($reputation['average_response_hours'])->toBe(10.0);
});
