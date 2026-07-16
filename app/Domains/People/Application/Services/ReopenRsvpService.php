<?php

namespace App\Domains\People\Application\Services;

use App\Domains\People\Domain\Events\RsvpReopened;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

class ReopenRsvpService
{
    public function handle(OccasionMember $member, User $actor): OccasionMember
    {
        $member->update([
            'rsvp_status' => null,
            'rsvp_responded_at' => null,
            'guest_count' => null,
            'rsvp_message' => null,
        ]);

        RsvpReopened::dispatch($member, $actor);

        return $member;
    }
}
