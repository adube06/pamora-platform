<?php

namespace App\Domains\Planning\Domain\Events;

use App\Domains\Planning\Domain\Models\Milestone;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MilestoneCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Milestone $milestone,
        public readonly User $actor,
    ) {}
}
