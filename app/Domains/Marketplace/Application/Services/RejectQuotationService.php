<?php

namespace App\Domains\Marketplace\Application\Services;

use App\Domains\Marketplace\Domain\Enums\QuotationStatus;
use App\Domains\Marketplace\Domain\Events\QuotationRejected;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RejectQuotationService
{
    public function handle(Quotation $quotation, User $actor): Quotation
    {
        if ($quotation->status !== QuotationStatus::Submitted) {
            throw ValidationException::withMessages([
                'status' => 'Only a submitted quotation can be rejected.',
            ]);
        }

        $quotation->update(['status' => QuotationStatus::Rejected]);

        QuotationRejected::dispatch($quotation->fresh(), $actor);

        return $quotation;
    }
}
