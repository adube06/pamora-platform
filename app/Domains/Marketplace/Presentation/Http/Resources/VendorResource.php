<?php

namespace App\Domains\Marketplace\Presentation\Http\Resources;

use App\Domains\Marketplace\Domain\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Vendor
 */
class VendorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'business_name' => $this->business_name,
            'categories' => $this->categories,
            'service_areas' => $this->service_areas,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'verification_status' => $this->verification_status->value,
            'status' => $this->status->value,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
