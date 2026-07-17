<?php

namespace App\Domains\Marketplace\Application\Services;

use App\Domains\Marketplace\Domain\Enums\QuotationStatus;
use App\Domains\Marketplace\Domain\Events\QuotationSubmitted;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SubmitQuotationService
{
    /**
     * @param  array{quoted_price: string|float, vendor_notes?: string|null}  $data
     */
    public function handle(Quotation $quotation, array $data, User $actor): Quotation
    {
        if ($quotation->status !== QuotationStatus::Draft) {
            throw ValidationException::withMessages([
                'quoted_price' => 'Only a pending quotation request can be submitted.',
            ]);
        }

        $quotation->update([
            ...$data,
            'status' => QuotationStatus::Submitted,
            'responded_at' => now(),
        ]);

        QuotationSubmitted::dispatch($quotation->fresh(), $actor);

        return $quotation;
    }
}
