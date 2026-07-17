<?php

namespace App\Domains\Marketplace\Presentation\Http\Resources;

use App\Domains\Marketplace\Domain\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Service
 */
class ServiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'category' => $this->category->value,
            'name' => $this->name,
            'description' => $this->description,
            'pricing_model' => $this->pricing_model->value,
            'price' => $this->price,
            'currency' => $this->currency,
            'estimated_duration' => $this->estimated_duration,
            'status' => $this->status->value,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
