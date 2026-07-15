<?php

namespace App\Domains\People\Application\Services;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\InvitationStatus;
use App\Domains\People\Domain\Events\MemberInvited;
use App\Domains\People\Domain\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Carbon;

class InviteMemberService
{
    private const EXPIRES_AFTER_DAYS = 7;

    /**
     * @param  array{email: string, responsibilities?: array<int, string>, permissions?: array<int, string>}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): Invitation
    {
        $invitation = Invitation::create([
            'occasion_id' => $occasion->id,
            'invited_by' => $actor->id,
            'email' => $data['email'],
            'status' => InvitationStatus::Pending,
            'responsibilities' => $data['responsibilities'] ?? [],
            'permissions' => $data['permissions'] ?? [],
            'token' => Invitation::generateToken(),
            'expires_at' => Carbon::now()->addDays(self::EXPIRES_AFTER_DAYS),
        ]);

        MemberInvited::dispatch($invitation, $actor);

        return $invitation;
    }
}
