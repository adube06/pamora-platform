<?php

namespace App\Domains\Planning\Presentation\Http\Resources;

use App\Domains\Planning\Domain\Models\TimelineEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TimelineEvent
 */
class TimelineEventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'scheduled_at' => $this->scheduled_at->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
