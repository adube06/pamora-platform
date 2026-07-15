<?php

namespace App\Domains\Planning\Presentation\Http\Resources;

use App\Domains\Planning\Domain\Models\Milestone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Milestone
 */
class MilestoneResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'is_achieved' => $this->isAchieved(),
            'tasks' => $this->whenLoaded('tasks', fn () => $this->tasks->map(fn ($task) => [
                'id' => $task->uuid,
                'title' => $task->title,
                'status' => $task->status->value,
            ])),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
