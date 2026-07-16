<?php

namespace App\Domains\People\Application\Services;

use App\Domains\People\Domain\Enums\InvitationStatus;
use App\Domains\People\Domain\Events\InvitationDeclined;
use App\Domains\People\Domain\Models\Invitation;
use Illuminate\Validation\ValidationException;

class DeclineInvitationService
{
    public function handle(Invitation $invitation): Invitation
    {
        if (! $invitation->isPending()) {
            throw ValidationException::withMessages([
                'invitation' => 'This invitation is no longer valid.',
            ]);
        }

        $invitation->update(['status' => InvitationStatus::Declined]);

        InvitationDeclined::dispatch($invitation->fresh());

        return $invitation;
    }
}
