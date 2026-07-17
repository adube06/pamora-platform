<?php

namespace App\Domains\Marketplace\Application\Services;

use App\Domains\Marketplace\Domain\Enums\BookingStatus;
use App\Domains\Marketplace\Domain\Events\ReviewPublished;
use App\Domains\Marketplace\Domain\Models\Booking;
use App\Domains\Marketplace\Domain\Models\Review;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class LeaveReviewService
{
    /**
     * @param  array{rating: int, comment?: string|null}  $data
     */
    public function handle(Booking $booking, array $data, User $actor): Review
    {
        if ($booking->status !== BookingStatus::Completed) {
            throw ValidationException::withMessages([
                'status' => 'Only a completed Booking can receive a Review.',
            ]);
        }

        if ($booking->review()->exists()) {
            throw ValidationException::withMessages([
                'status' => 'This Booking already has a Review.',
            ]);
        }

        $review = Review::create([
            'booking_id' => $booking->id,
            'occasion_id' => $booking->occasion_id,
            'service_id' => $booking->service_id,
            'reviewed_by' => $actor->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'published_at' => now(),
        ]);

        ReviewPublished::dispatch($review->fresh(), $actor);

        return $review;
    }
}
