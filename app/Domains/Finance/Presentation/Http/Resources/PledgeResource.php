<?php

namespace App\Domains\Finance\Presentation\Http\Resources;

use App\Domains\Finance\Domain\Models\Pledge;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Pledge
 */
class PledgeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'pledgor_name' => $this->pledgor_name,
            'pledgor_phone' => $this->pledgor_phone,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'message' => $this->message,
            'pledged_at' => $this->pledged_at->toDateString(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
