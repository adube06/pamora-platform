<?php

namespace App\Domains\Marketplace\Domain\Events;

use App\Domains\Marketplace\Domain\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Review $review,
        public readonly User $actor,
    ) {}
}
