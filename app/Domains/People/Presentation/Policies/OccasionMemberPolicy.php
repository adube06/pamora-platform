<?php

namespace App\Domains\People\Presentation\Policies;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Enums\Permission;
use App\Models\User;

/**
 * Authorization for People-domain actions (inviting/removing members).
 * These are checked against the caller's OccasionMember for the target
 * Occasion, not against a Model class — People-domain actions are always
 * scoped to "can this user manage membership on this Occasion."
 */
class OccasionMemberPolicy
{
    public function invite(User $user, Occasion $occasion): bool
    {
        return $occasion->memberFor($user)?->hasPermission(Permission::PeopleInviteMember) ?? false;
    }

    public function remove(User $user, Occasion $occasion): bool
    {
        return $occasion->memberFor($user)?->hasPermission(Permission::PeopleRemoveMember) ?? false;
    }
}
