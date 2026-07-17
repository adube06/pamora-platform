<?php

namespace App\Domains\Marketplace\Presentation\Http\Resources;

use App\Domains\Marketplace\Domain\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Booking
 */
class BookingResource extends JsonResource
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
            'status' => $this->status->value,
            'agreed_price' => $this->agreed_price,
            'currency' => $this->currency,
            'notes' => $this->notes,
            'confirmed_at' => $this->confirmed_at->toIso8601String(),
        ];
    }
}
