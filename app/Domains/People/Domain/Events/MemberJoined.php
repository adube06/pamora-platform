<?php

namespace App\Domains\People\Domain\Events;

use App\Domains\People\Domain\Models\OccasionMember;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemberJoined
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly OccasionMember $member) {}
}
