<?php

use App\Domains\Marketplace\Application\Services\ApproveVendorService;
use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;
use Illuminate\Validation\ValidationException;

it('approves a pending vendor application', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $vendor = Vendor::factory()->create(['verification_status' => VendorVerificationStatus::Pending]);

    app(ApproveVendorService::class)->handle($vendor, $admin);

    expect($vendor->fresh()->verification_status)->toBe(VendorVerificationStatus::Verified);
});

it('rejects approving a vendor that is not pending', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $vendor = Vendor::factory()->create(['verification_status' => VendorVerificationStatus::Verified]);

    app(ApproveVendorService::class)->handle($vendor, $admin);
})->throws(ValidationException::class);
