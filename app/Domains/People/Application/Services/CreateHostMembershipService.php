<?php

namespace App\Domains\People\Application\Services;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\OccasionMemberStatus;
use App\Domains\People\Domain\Events\MemberJoined;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Shared\Domain\Enums\Permission;
use App\Models\User;

/**
 * Creates the Host's OccasionMember record when an Occasion is created
 * (Occasion PRD FR-001: "Host assigned automatically"). This is the only
 * path that creates a membership without an Invitation.
 */
class CreateHostMembershipService
{
    public function handle(Occasion $occasion, User $host): OccasionMember
    {
        $member = OccasionMember::create([
            'occasion_id' => $occasion->id,
            'user_id' => $host->id,
            'invitation_id' => null,
            'status' => OccasionMemberStatus::Active,
            'responsibilities' => [],
            'permissions' => Permission::hostDefaults(),
        ]);

        MemberJoined::dispatch($member);

        return $member;
    }
}
