<?php

namespace App\Domains\Marketplace\Presentation\Http\Resources;

use App\Domains\Marketplace\Domain\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Quotation
 */
class QuotationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'service_id' => $this->service->uuid,
            'service_name' => $this->service->name,
            'message' => $this->message,
            'status' => $this->status->value,
            'quoted_price' => $this->quoted_price,
            'currency' => $this->currency,
            'vendor_notes' => $this->vendor_notes,
            'requested_at' => $this->requested_at->toIso8601String(),
            'responded_at' => $this->responded_at?->toIso8601String(),
        ];
    }
}
