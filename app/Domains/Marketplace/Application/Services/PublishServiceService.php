<?php

namespace App\Domains\Marketplace\Application\Services;

use App\Domains\Marketplace\Domain\Enums\ServiceStatus;
use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Marketplace\Domain\Events\ServicePublished;
use App\Domains\Marketplace\Domain\Models\Service;
use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PublishServiceService
{
    /**
     * @param  array{category: string, name: string, description?: string|null, pricing_model: string, price?: string|float|null, estimated_duration?: string|null}  $data
     */
    public function handle(Vendor $vendor, array $data, User $actor): Service
    {
        if ($vendor->verification_status !== VendorVerificationStatus::Verified) {
            throw ValidationException::withMessages([
                'name' => 'Only an approved Vendor may publish Services.',
            ]);
        }

        $service = $vendor->services()->create([
            ...$data,
            'currency' => 'TZS',
            'status' => ServiceStatus::Active,
        ]);

        ServicePublished::dispatch($service, $actor);

        return $service;
    }
}
