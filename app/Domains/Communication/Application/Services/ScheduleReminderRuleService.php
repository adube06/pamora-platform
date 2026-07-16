<?php

namespace App\Domains\Communication\Application\Services;

use App\Domains\Communication\Domain\Events\ReminderRuleScheduled;
use App\Domains\Communication\Domain\Models\ReminderRule;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;

class ScheduleReminderRuleService
{
    use GuardsAgainstArchivedOccasion;

    /**
     * @param  array{timeline_event_id: int, offset_minutes: int}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): ReminderRule
    {
        $this->ensureOccasionAcceptsActivity($occasion);

        $reminderRule = ReminderRule::create([
            ...$data,
            'occasion_id' => $occasion->id,
            'created_by' => $actor->id,
        ]);

        ReminderRuleScheduled::dispatch($reminderRule, $actor);

        return $reminderRule;
    }
}
