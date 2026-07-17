<?php

namespace App\Domains\Marketplace\Application\Services;

use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Events\VendorRejected;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RejectVendorService
{
    public function handle(Vendor $vendor, User $admin): Vendor
    {
        if ($vendor->verification_status !== VendorVerificationStatus::Pending) {
            throw ValidationException::withMessages([
                'verification_status' => 'Only a pending Vendor application can be rejected.',
            ]);
        }

        $vendor->update(['verification_status' => VendorVerificationStatus::Rejected]);

        VendorRejected::dispatch($vendor->fresh(), $admin);

        return $vendor;
    }
}
