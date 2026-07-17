<?php

namespace App\Domains\Marketplace\Presentation\Http\Resources;

use App\Domains\Marketplace\Domain\Models\AvailabilityBlock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AvailabilityBlock
 */
class AvailabilityBlockResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date->toDateString(),
            'reason' => $this->reason,
        ];
    }
}
