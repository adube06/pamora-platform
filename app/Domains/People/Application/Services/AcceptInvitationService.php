<?php

namespace App\Domains\People\Application\Services;

use App\Domains\People\Domain\Enums\InvitationStatus;
use App\Domains\People\Domain\Enums\OccasionMemberStatus;
use App\Domains\People\Domain\Events\MemberJoined;
use App\Domains\People\Domain\Models\Invitation;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcceptInvitationService
{
    public function handle(Invitation $invitation, User $user): OccasionMember
    {
        if (! $invitation->isPending()) {
            throw ValidationException::withMessages([
                'invitation' => 'This invitation is no longer valid.',
            ]);
        }

        if (strcasecmp($invitation->email, $user->email) !== 0) {
            throw ValidationException::withMessages([
                'invitation' => 'This invitation was sent to a different email address.',
            ]);
        }

        return DB::transaction(function () use ($invitation, $user) {
            $member = OccasionMember::create([
                'occasion_id' => $invitation->occasion_id,
                'user_id' => $user->id,
                'invitation_id' => $invitation->id,
                'status' => OccasionMemberStatus::Active,
                'responsibilities' => $invitation->responsibilities,
                'permissions' => $invitation->permissions,
            ]);

            $invitation->update([
                'status' => InvitationStatus::Accepted,
                'accepted_at' => now(),
            ]);

            MemberJoined::dispatch($member);

            return $member;
        });
    }
}
