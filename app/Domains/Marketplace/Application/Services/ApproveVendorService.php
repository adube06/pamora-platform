<?php

namespace App\Domains\Marketplace\Application\Services;

use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Events\VendorApproved;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ApproveVendorService
{
    public function handle(Vendor $vendor, User $admin): Vendor
    {
        if ($vendor->verification_status !== VendorVerificationStatus::Pending) {
            throw ValidationException::withMessages([
                'verification_status' => 'Only a pending Vendor application can be approved.',
            ]);
        }

        $vendor->update(['verification_status' => VendorVerificationStatus::Verified]);

        VendorApproved::dispatch($vendor->fresh(), $admin);

        return $vendor;
    }
}
