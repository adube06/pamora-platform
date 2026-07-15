<?php

namespace App\Domains\Finance\Presentation\Http\Resources;

use App\Domains\Finance\Domain\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Contribution
 */
class ContributionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'contributor_name' => $this->contributor_name,
            'contributor_phone' => $this->contributor_phone,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'method' => $this->method->value,
            'message' => $this->message,
            'contributed_at' => $this->contributed_at->toDateString(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
