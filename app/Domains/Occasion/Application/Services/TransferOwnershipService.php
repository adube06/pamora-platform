<?php

namespace App\Domains\Occasion\Application\Services;

use App\Domains\Occasion\Domain\Events\OccasionOwnershipTransferred;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Application\Services\ReassignHostRoleService;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;

class TransferOwnershipService
{
    public function __construct(
        private readonly ReassignHostRoleService $reassignHostRoleService,
    ) {}

    public function handle(Occasion $occasion, OccasionMember $newHostMember, User $actor): Occasion
    {
        $currentHostMember = $occasion->members()->where('user_id', $occasion->host_id)->firstOrFail();
        $previousHost = $currentHostMember->user;

        // Cross-domain orchestration happens here, at the Application
        // layer, through People's own Application Service — Occasion
        // never writes to occasion_members directly (Constitution
        // Article V), same pattern CreateOccasionService uses.
        $this->reassignHostRoleService->handle($currentHostMember, $newHostMember);

        $occasion->update([
            'host_id' => $newHostMember->user_id,
            'updated_by' => $actor->id,
        ]);

        OccasionOwnershipTransferred::dispatch($occasion->fresh(), $previousHost, $newHostMember->user, $actor);

        return $occasion;
    }
}
