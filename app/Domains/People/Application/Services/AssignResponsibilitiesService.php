<?php

namespace App\Domains\People\Application\Services;

use App\Domains\People\Domain\Events\ResponsibilityAssigned;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;

class AssignResponsibilitiesService
{
    use GuardsAgainstArchivedOccasion;

    /**
     * @param  array<int, string>  $responsibilities
     */
    public function handle(OccasionMember $member, array $responsibilities, User $actor): OccasionMember
    {
        $this->ensureOccasionAcceptsActivity($member->occasion);

        $member->update(['responsibilities' => $responsibilities]);

        ResponsibilityAssigned::dispatch($member->fresh(), $actor);

        return $member;
    }
}
