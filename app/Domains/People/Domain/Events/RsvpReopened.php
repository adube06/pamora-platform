<?php

namespace App\Domains\People\Domain\Events;

use App\Domains\People\Domain\Models\OccasionMember;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RsvpReopened
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly OccasionMember $member,
        public readonly User $actor,
    ) {}
}
