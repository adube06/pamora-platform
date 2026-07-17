<?php

use App\Domains\Marketplace\Domain\Enums\BookingStatus;
use App\Domains\Marketplace\Domain\Models\Booking;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Domains\Marketplace\Domain\Models\Review;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

it('lets a host leave a review on a completed booking', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $vendor = Vendor::factory()->create();
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
        'status' => BookingStatus::Completed,
    ]);

    $this->actingAs($host)
        ->post("/bookings/{$booking->uuid}/review", ['rating' => 5, 'comment' => 'Excellent service!'])
        ->assertSessionHasNoErrors();

    $review = Review::firstWhere('booking_id', $booking->id);

    expect($review)->not->toBeNull()
        ->and($review->rating)->toBe(5)
        ->and($review->comment)->toBe('Excellent service!')
        ->and($review->occasion_id)->toBe($occasion->id)
        ->and($review->service_id)->toBe($service->id)
        ->and($review->reviewed_by)->toBe($host->id);
});

it('rejects reviewing a booking that is not completed', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $vendor = Vendor::factory()->create();
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $quotation = Quotation::factory()->create(['occasion_id' => $occasion->id, 'service_id' => $service->id]);
    $booking = Booking::factory()->create([
        'occasion_id' => $occasion->id,
        'service_id' => $service->id,
        'quotation_id' => $quotation->id,
        'status' => BookingStatus::Confirmed,
    ]);

    $this->actingAs($host)
        ->post("/bookings/{$booking->uuid}/review", ['rating' => 5])
        ->assertSessionHasErrors('status');

    expect(Review::where('booking_id', $booking->id)->exists())->toBeFalse();
});

it('rejects a second review on the same booking', function () {
    $host = User::factory()->create();
    $occasion = Occasion::factory()->create(['host_id' => $host->id]);
    OccasionMember::factory()->host()->create(['occasion_id' => $occasion->id, 'user_id' => $host->id]);

    $vendor = Vendor::factory()->create();
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $quotation = Quotation::factory()->create(['occasion_id' => $occasion->id, 'service_id' => $service->id]);
    $booking = Booking::factory()->create([
        'occasion_id' => $occasion->id,
        'service_id' => $service->id,
        'quotation_id' => $quotation->id,
        'status' => BookingStatus::Completed,
    ]);

    $this->actingAs($host)->post("/bookings/{$booking->uuid}/review", ['rating' => 4])->assertSessionHasNoErrors();

    $this->actingAs($host)
        ->post("/bookings/{$booking->uuid}/review", ['rating' => 2])
        ->assertSessionHasErrors('status');

    expect(Review::where('booking_id', $booking->id)->count())->toBe(1);
});

it('prevents a member without marketplace.leave_review from submitting a review', function () {
    $occasion = Occasion::factory()->create();
    $guestUser = User::factory()->create();
    OccasionMember::factory()->create([
        'occasion_id' => $occasion->id,
        'user_id' => $guestUser->id,
        'permissions' => [],
    ]);

    $vendor = Vendor::factory()->create();
    $service = Service::factory()->create(['vendor_id' => $vendor->id]);
    $quotation = Quotation::factory()->create(['occasion_id' => $occasion->id, 'service_id' => $service->id]);
    $booking = Booking::factory()->create([
        'occasion_id' => $occasion->id,
        'service_id' => $service->id,
        'quotation_id' => $quotation->id,
        'status' => BookingStatus::Completed,
    ]);

    $this->actingAs($guestUser)
        ->post("/bookings/{$booking->uuid}/review", ['rating' => 5])
        ->assertForbidden();

    expect(Review::where('booking_id', $booking->id)->exists())->toBeFalse();
});
