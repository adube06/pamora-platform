<?php

namespace App\Domains\Marketplace\Domain\Events;

use App\Domains\Marketplace\Domain\Models\RentalItem;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RentalItemPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly RentalItem $rentalItem,
        public readonly User $actor,
    ) {}
}
