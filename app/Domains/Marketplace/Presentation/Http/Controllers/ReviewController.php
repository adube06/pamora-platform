<?php

namespace App\Domains\Marketplace\Presentation\Http\Controllers;

use App\Domains\Marketplace\Application\Services\LeaveReviewService;
use App\Domains\Marketplace\Domain\Models\Booking;
use App\Domains\Marketplace\Presentation\Http\Requests\LeaveReviewRequest;
use Illuminate\Http\RedirectResponse;

class ReviewController
{
    public function store(LeaveReviewRequest $request, Booking $booking, LeaveReviewService $service): RedirectResponse
    {
        $service->handle($booking, $request->validated(), $request->user());

        return back()->with('success', 'Review submitted.');
    }
}
