<?php

namespace App\Domains\Marketplace\Domain\Events;

use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VendorApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Vendor $vendor,
        public readonly User $actor,
    ) {}
}
