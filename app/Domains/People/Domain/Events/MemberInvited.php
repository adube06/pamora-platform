<?php

namespace App\Domains\People\Domain\Events;

use App\Domains\People\Domain\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemberInvited
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Invitation $invitation,
        public readonly User $actor,
    ) {}
}
