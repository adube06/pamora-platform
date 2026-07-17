<?php

namespace App\Domains\Marketplace\Application\Services;

use App\Domains\Marketplace\Domain\Enums\VendorStatus;
use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Events\VendorApplied;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ApplyAsVendorService
{
    /**
     * @param  array{business_name: string, categories: array<int, string>, service_areas?: array<int, string>|null, contact_email: string, contact_phone: string}  $data
     */
    public function handle(User $user, array $data): Vendor
    {
        // BR-023: every Vendor owns exactly one Vendor Profile — modeled
        // here as one Vendor row per owner (unique owner_id).
        if (Vendor::where('owner_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'business_name' => 'You have already applied to become a Vendor.',
            ]);
        }

        $vendor = Vendor::create([
            ...$data,
            'owner_id' => $user->id,
            'verification_status' => VendorVerificationStatus::Pending,
            'status' => VendorStatus::Active,
        ]);

        VendorApplied::dispatch($vendor, $user);

        return $vendor;
    }
}
