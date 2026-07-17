<?php

namespace App\Domains\Marketplace\Presentation\Http\Controllers;

use App\Domains\Marketplace\Application\Services\CompleteBookingService;
use App\Domains\Marketplace\Domain\Models\Booking;
use App\Domains\Marketplace\Presentation\Http\Requests\CompleteBookingRequest;
use Illuminate\Http\RedirectResponse;

class BookingController
{
    public function complete(CompleteBookingRequest $request, Booking $booking, CompleteBookingService $service): RedirectResponse
    {
        $service->handle($booking, $request->user());

        return back()->with('success', 'Booking marked complete.');
    }
}
