<?php

namespace App\Domains\Marketplace\Application\Services;

use App\Domains\Marketplace\Domain\Enums\QuotationStatus;
use App\Domains\Marketplace\Domain\Events\QuotationAccepted;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AcceptQuotationService
{
    public function handle(Quotation $quotation, User $actor): Quotation
    {
        if ($quotation->status !== QuotationStatus::Submitted) {
            throw ValidationException::withMessages([
                'status' => 'Only a submitted quotation can be accepted.',
            ]);
        }

        $quotation->update(['status' => QuotationStatus::Accepted]);

        QuotationAccepted::dispatch($quotation->fresh(), $actor);

        return $quotation;
    }
}
