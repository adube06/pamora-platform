<?php

namespace App\Domains\Marketplace\Presentation\Http\Controllers\Api;

use App\Domains\Marketplace\Application\Services\LeaveReviewService;
use App\Domains\Marketplace\Domain\Models\Booking;
use App\Domains\Marketplace\Presentation\Http\Requests\LeaveReviewRequest;
use App\Domains\Marketplace\Presentation\Http\Resources\ReviewResource;
use Illuminate\Http\JsonResponse;

class ReviewController
{
    public function store(LeaveReviewRequest $request, Booking $booking, LeaveReviewService $service): JsonResponse
    {
        $review = $service->handle($booking, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => new ReviewResource($review),
        ], 201);
    }
}
