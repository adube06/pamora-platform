<?php

namespace App\Domains\People\Presentation\Http\Resources;

use App\Domains\People\Domain\Models\OccasionMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OccasionMember
 */
class OccasionMemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'role' => $this->role->value,
            'status' => $this->status->value,
            'responsibilities' => $this->responsibilities ?? [],
            'rsvp_status' => $this->rsvp_status?->value,
            'guest_count' => $this->guest_count,
            'rsvp_message' => $this->rsvp_message,
            'joined_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
