<?php

namespace App\Domains\People\Application\Services;

use App\Domains\People\Domain\Enums\OccasionMemberStatus;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
use Illuminate\Validation\ValidationException;

/**
 * The People-domain half of Occasion Ownership Transfer — updates the two
 * OccasionMember rows involved. Occasion's own TransferOwnershipService
 * orchestrates this alongside the occasions.host_id write, keeping "Occasion
 * never writes to occasion_members directly" intact (Constitution Article V).
 */
class ReassignHostRoleService
{
    public function handle(OccasionMember $currentHost, OccasionMember $newHost): void
    {
        if ($newHost->occasion_id !== $currentHost->occasion_id) {
            throw ValidationException::withMessages([
                'member' => 'Ownership can only be transferred to a member of the same Occasion.',
            ]);
        }

        if ($newHost->id === $currentHost->id) {
            throw ValidationException::withMessages([
                'member' => 'This member is already the Host.',
            ]);
        }

        if ($newHost->status !== OccasionMemberStatus::Active) {
            throw ValidationException::withMessages([
                'member' => 'Ownership can only be transferred to an active member.',
            ]);
        }

        if (in_array($newHost->role, [Role::Guest, Role::Observer], true)) {
            throw ValidationException::withMessages([
                'member' => 'A Guest or Observer is not eligible to become Host.',
            ]);
        }

        $currentHost->update([
            'role' => Role::Chairperson,
            'permissions' => Role::Chairperson->permissions(),
        ]);

        $newHost->update([
            'role' => Role::Host,
            'permissions' => Role::Host->permissions(),
        ]);
    }
}
