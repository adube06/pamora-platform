<?php

namespace App\Domains\Planning\Application\Services;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Events\TimelineEventScheduled;
use App\Domains\Planning\Domain\Models\TimelineEvent;
use App\Models\User;

class ScheduleTimelineEventService
{
    /**
     * @param  array{name: string, scheduled_at: string}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): TimelineEvent
    {
        $timelineEvent = TimelineEvent::create([
            ...$data,
            'occasion_id' => $occasion->id,
            'created_by' => $actor->id,
        ]);

        TimelineEventScheduled::dispatch($timelineEvent, $actor);

        return $timelineEvent;
    }
}
