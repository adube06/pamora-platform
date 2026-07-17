<?php

namespace App\Domains\Marketplace\Application\Services;

use App\Domains\Marketplace\Domain\Enums\QuotationStatus;
use App\Domains\Marketplace\Domain\Enums\ServiceStatus;
use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Events\QuotationRequested;
use App\Domains\Marketplace\Domain\Models\Quotation;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RequestQuotationService
{
    use GuardsAgainstArchivedOccasion;

    /**
     * @param  array{message?: string|null}  $data
     */
    public function handle(Occasion $occasion, Service $service, array $data, User $actor): Quotation
    {
        $this->ensureOccasionAcceptsActivity($occasion);

        if ($service->vendor->verification_status !== VendorVerificationStatus::Verified || $service->status !== ServiceStatus::Active) {
            throw ValidationException::withMessages([
                'service_id' => 'This Service is not currently accepting quotation requests.',
            ]);
        }

        $quotation = Quotation::create([
            ...$data,
            'occasion_id' => $occasion->id,
            'service_id' => $service->id,
            'requested_by' => $actor->id,
            'status' => QuotationStatus::Draft,
            'requested_at' => now(),
        ]);

        QuotationRequested::dispatch($quotation, $actor);

        return $quotation;
    }
}
