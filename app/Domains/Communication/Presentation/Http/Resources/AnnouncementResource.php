<?php

namespace App\Domains\Communication\Presentation\Http\Resources;

use App\Domains\Communication\Domain\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Announcement
 */
class AnnouncementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'title' => $this->title,
            'message' => $this->message,
            'audience' => $this->audience,
            'status' => $this->status,
            'published_at' => $this->published_at->toIso8601String(),
            'author' => $this->whenLoaded('createdBy', fn () => $this->createdBy->name),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
