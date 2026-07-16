<?php

namespace App\Domains\People\Domain\Events;

use App\Domains\People\Domain\Models\OccasionMember;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Self-service action — the actor is always the responding member,
 * same no-separate-actor shape as MemberJoined.
 */
class RsvpSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly OccasionMember $member) {}
}
