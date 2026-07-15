<?php

namespace App\Domains\Planning\Domain\Events;

use App\Domains\Planning\Domain\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimelineEventScheduled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly TimelineEvent $timelineEvent,
        public readonly User $actor,
    ) {}
}
