<?php

use App\Domains\Marketplace\Application\Services\RejectVendorService;
use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;
use Illuminate\Validation\ValidationException;

it('rejects a pending vendor application', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $vendor = Vendor::factory()->create(['verification_status' => VendorVerificationStatus::Pending]);

    app(RejectVendorService::class)->handle($vendor, $admin);

    expect($vendor->fresh()->verification_status)->toBe(VendorVerificationStatus::Rejected);
});

it('rejects rejecting a vendor that is not pending', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $vendor = Vendor::factory()->create(['verification_status' => VendorVerificationStatus::Rejected]);

    app(RejectVendorService::class)->handle($vendor, $admin);
})->throws(ValidationException::class);
