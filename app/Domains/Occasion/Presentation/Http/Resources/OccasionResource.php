<?php

namespace App\Domains\Occasion\Presentation\Http\Resources;

use App\Domains\Occasion\Domain\Models\Occasion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Occasion
 */
class OccasionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->title,
            'type' => $this->type->value,
            'description' => $this->description,
            'primary_date' => $this->primary_date?->toDateString(),
            'timezone' => $this->timezone,
            'location' => $this->location,
            'visibility' => $this->visibility->value,
            'status' => $this->status->value,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
