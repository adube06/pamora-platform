<?php

namespace App\Domains\People\Application\Services;

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Events\MemberRemoved;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RemoveMemberService
{
    public function handle(OccasionMember $member, User $actor): void
    {
        if ($member->occasion->status === OccasionStatus::Completed) {
            throw ValidationException::withMessages([
                'member' => 'Members cannot be removed once the Occasion is completed.',
            ]);
        }

        if ($member->role === Role::Host) {
            throw ValidationException::withMessages([
                'member' => 'The Host cannot be removed — transfer ownership first.',
            ]);
        }

        $member->delete();

        MemberRemoved::dispatch($member, $actor);
    }
}
