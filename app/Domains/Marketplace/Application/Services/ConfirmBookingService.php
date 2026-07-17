<?php

namespace App\Domains\Marketplace\Application\Services;

use App\Domains\Marketplace\Domain\Enums\BookingStatus;
use App\Domains\Marketplace\Domain\Enums\QuotationStatus;
use App\Domains\Marketplace\Domain\Events\BookingConfirmed;
use App\Domains\Marketplace\Domain\Models\Booking;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ConfirmBookingService
{
    public function handle(Quotation $quotation, User $actor): Booking
    {
        if ($quotation->status !== QuotationStatus::Accepted) {
            throw ValidationException::withMessages([
                'status' => 'Only an accepted quotation can be confirmed as a Booking.',
            ]);
        }

        if ($quotation->booking()->exists()) {
            throw ValidationException::withMessages([
                'status' => 'This quotation has already been confirmed as a Booking.',
            ]);
        }

        $occasion = $quotation->occasion;

        if ($occasion->primary_date && ! $quotation->service->vendor->isAvailableOn($occasion->primary_date)) {
            throw ValidationException::withMessages([
                'status' => 'The Vendor is not available on the Occasion date.',
            ]);
        }

        $booking = Booking::create([
            'occasion_id' => $quotation->occasion_id,
            'service_id' => $quotation->service_id,
            'quotation_id' => $quotation->id,
            'confirmed_by' => $actor->id,
            'status' => BookingStatus::Confirmed,
            'agreed_price' => $quotation->quoted_price,
            'currency' => $quotation->currency,
            'confirmed_at' => now(),
        ]);

        BookingConfirmed::dispatch($booking->fresh(), $actor);

        return $booking;
    }
}
