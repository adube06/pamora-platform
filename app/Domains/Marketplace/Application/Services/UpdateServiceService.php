<?php

namespace App\Domains\Marketplace\Application\Services;

use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Events\ServiceUpdated;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateServiceService
{
    /**
     * @param  array{category: string, name: string, description?: string|null, pricing_model: string, price?: string|float|null, estimated_duration?: string|null}  $data
     */
    public function handle(Service $service, array $data, User $actor): Service
    {
        if ($service->vendor->verification_status !== VendorVerificationStatus::Verified) {
            throw ValidationException::withMessages([
                'name' => 'Only an approved Vendor may edit Services.',
            ]);
        }

        $service->update($data);

        ServiceUpdated::dispatch($service->fresh(), $actor);

        return $service;
    }
}
