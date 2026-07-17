<?php

namespace App\Domains\Marketplace\Domain\Events;

use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuotationSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Quotation $quotation,
        public readonly User $actor,
    ) {}
}
