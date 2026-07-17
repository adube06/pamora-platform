<?php

namespace App\Domains\People\Application\Services;

use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Events\MemberRoleUpdated;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateMemberRoleService
{
    use GuardsAgainstArchivedOccasion;

    public function handle(OccasionMember $member, Role $newRole, User $actor): OccasionMember
    {
        $this->ensureOccasionAcceptsActivity($member->occasion);

        if ($member->role === Role::Host || $newRole === Role::Host) {
            throw ValidationException::withMessages([
                'role' => 'The Host\'s role cannot be changed here — transfer ownership instead.',
            ]);
        }

        $previousRole = $member->role;

        $member->update([
            'role' => $newRole,
            'permissions' => $newRole->permissions(),
        ]);

        MemberRoleUpdated::dispatch($member->fresh(), $previousRole, $actor);

        return $member;
    }
}
