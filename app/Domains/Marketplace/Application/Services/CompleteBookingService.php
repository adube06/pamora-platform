<?php

namespace App\Domains\Marketplace\Application\Services;

use App\Domains\Marketplace\Domain\Enums\BookingStatus;
use App\Domains\Marketplace\Domain\Events\BookingCompleted;
use App\Domains\Marketplace\Domain\Models\Booking;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CompleteBookingService
{
    public function handle(Booking $booking, User $actor): Booking
    {
        if ($booking->status !== BookingStatus::Confirmed) {
            throw ValidationException::withMessages([
                'status' => 'Only a confirmed Booking can be marked complete.',
            ]);
        }

        $booking->update(['status' => BookingStatus::Completed]);

        BookingCompleted::dispatch($booking->fresh(), $actor);

        return $booking;
    }
}
