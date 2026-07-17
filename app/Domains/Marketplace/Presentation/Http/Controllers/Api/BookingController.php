<?php

namespace App\Domains\Marketplace\Presentation\Http\Controllers\Api;

use App\Domains\Marketplace\Application\Services\CompleteBookingService;
use App\Domains\Marketplace\Domain\Models\Booking;
use App\Domains\Marketplace\Presentation\Http\Requests\CompleteBookingRequest;
use App\Domains\Marketplace\Presentation\Http\Resources\BookingResource;
use Illuminate\Http\JsonResponse;

class BookingController
{
    public function complete(CompleteBookingRequest $request, Booking $booking, CompleteBookingService $service): JsonResponse
    {
        $completed = $service->handle($booking, $request->user());

        return response()->json([
            'success' => true,
            'data' => new BookingResource($completed),
        ]);
    }
}
