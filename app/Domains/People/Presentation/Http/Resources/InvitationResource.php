<?php

namespace App\Domains\People\Presentation\Http\Resources;

use App\Domains\People\Domain\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Deliberately omits `token` — it's a bearer credential for accepting the
 * invitation and is only ever returned directly to the inviter at creation
 * time (InvitationController::store), never in a list any occasion member
 * can read.
 *
 * @mixin Invitation
 */
class InvitationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'email' => $this->email,
            'role' => $this->role->value,
            'status' => $this->status->value,
            'notes' => $this->notes,
            'invited_by' => [
                'id' => $this->invitedBy->id,
                'name' => $this->invitedBy->name,
            ],
            'expires_at' => $this->expires_at?->toIso8601String(),
        ];
    }
}
