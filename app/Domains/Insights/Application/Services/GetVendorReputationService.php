<?php

namespace App\Domains\Insights\Application\Services;

use App\Domains\Marketplace\Domain\Enums\BookingStatus;
use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Models\Booking;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Domains\Marketplace\Domain\Models\Review;
use App\Domains\Marketplace\Domain\Models\Vendor;

/**
 * ADR-008 (Analytics Is Read-Only) / ADR-004 (never store derived state):
 * computed fresh from Marketplace rows every call, same shape as
 * GetReadinessScoreService. Cancellation rate is left out entirely —
 * BookingStatus::Cancelled/Declined have no code path creating them yet,
 * so that signal would always be 0/0, not "low data." Response time has
 * no PRD-defined target to normalize against, so it's returned as a raw
 * stat rather than folded into the score, same as GetTaskProgressService
 * returning both raw counts and a normalized percentage.
 *
 * @phpstan-type ReputationSignal array{key: string, label: string, value: int}
 * @phpstan-type VendorReputation array{
 *     score: ?int,
 *     signals: list<ReputationSignal>,
 *     review_count: int,
 *     average_response_hours: ?float,
 * }
 */
class GetVendorReputationService
{
    /**
     * @return VendorReputation
     */
    public function handle(Vendor $vendor): array
    {
        $signals = [
            $this->verificationSignal($vendor),
            $this->profileCompletenessSignal($vendor),
        ];

        $reviewSignal = $this->reviewRatingSignal($vendor);
        if ($reviewSignal !== null) {
            $signals[] = $reviewSignal;
        }

        $completionSignal = $this->bookingCompletionSignal($vendor);
        if ($completionSignal !== null) {
            $signals[] = $completionSignal;
        }

        return [
            'score' => (int) round(array_sum(array_column($signals, 'value')) / count($signals)),
            'signals' => $signals,
            'review_count' => Review::whereIn('service_id', $vendor->services()->pluck('id'))->count(),
            'average_response_hours' => $this->averageResponseHours($vendor),
        ];
    }

    /**
     * @return ReputationSignal
     */
    private function verificationSignal(Vendor $vendor): array
    {
        return [
            'key' => 'verification_status',
            'label' => 'Verification Status',
            'value' => match ($vendor->verification_status) {
                VendorVerificationStatus::Verified => 100,
                VendorVerificationStatus::Pending => 50,
                VendorVerificationStatus::Rejected => 0,
            },
        ];
    }

    /**
     * @return ReputationSignal
     */
    private function profileCompletenessSignal(Vendor $vendor): array
    {
        $checks = [
            ! empty($vendor->service_areas),
            $vendor->services()->count() > 0,
            $vendor->rentalItems()->count() > 0,
        ];

        $complete = count(array_filter($checks));

        return [
            'key' => 'profile_completeness',
            'label' => 'Profile Completeness',
            'value' => (int) round(($complete / count($checks)) * 100),
        ];
    }

    /**
     * @return ReputationSignal|null
     */
    private function reviewRatingSignal(Vendor $vendor): ?array
    {
        $serviceIds = $vendor->services()->pluck('id');
        $averageRating = Review::whereIn('service_id', $serviceIds)->avg('rating');

        if ($averageRating === null) {
            return null;
        }

        return [
            'key' => 'review_rating',
            'label' => 'Review Rating',
            'value' => (int) round(($averageRating / 5) * 100),
        ];
    }

    /**
     * @return ReputationSignal|null
     */
    private function bookingCompletionSignal(Vendor $vendor): ?array
    {
        $serviceIds = $vendor->services()->pluck('id');

        $total = Booking::whereIn('service_id', $serviceIds)
            ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::InProgress, BookingStatus::Completed])
            ->count();

        if ($total === 0) {
            return null;
        }

        $completed = Booking::whereIn('service_id', $serviceIds)
            ->where('status', BookingStatus::Completed)
            ->count();

        return [
            'key' => 'booking_completion_rate',
            'label' => 'Booking Completion Rate',
            'value' => (int) round(($completed / $total) * 100),
        ];
    }

    private function averageResponseHours(Vendor $vendor): ?float
    {
        $serviceIds = $vendor->services()->pluck('id');

        $quotations = Quotation::whereIn('service_id', $serviceIds)
            ->whereNotNull('responded_at')
            ->get(['requested_at', 'responded_at']);

        if ($quotations->isEmpty()) {
            return null;
        }

        $totalHours = $quotations->sum(fn (Quotation $quotation) => $quotation->requested_at->diffInHours($quotation->responded_at));

        return round($totalHours / $quotations->count(), 1);
    }
}
