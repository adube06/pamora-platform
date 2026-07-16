<?php

namespace App\Domains\Communication\Presentation\Http\Resources;

use App\Domains\Communication\Domain\Models\ReminderRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ReminderRule
 */
class ReminderRuleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'offset_minutes' => $this->offset_minutes,
            'triggered_at' => $this->triggered_at?->toIso8601String(),
            'timeline_event' => [
                'id' => $this->timelineEvent->uuid,
                'name' => $this->timelineEvent->name,
                'scheduled_at' => $this->timelineEvent->scheduled_at->toIso8601String(),
            ],
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
