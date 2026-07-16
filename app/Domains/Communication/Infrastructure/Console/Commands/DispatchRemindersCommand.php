<?php

namespace App\Domains\Communication\Infrastructure\Console\Commands;

use App\Domains\Communication\Domain\Events\ReminderTriggered;
use App\Domains\Communication\Domain\Models\ReminderRule;
use Illuminate\Console\Command;

/**
 * Scheduled every 5 minutes (bootstrap/app.php's withSchedule()). Fires
 * each pending Reminder Rule exactly once, guarded by triggered_at.
 */
class DispatchRemindersCommand extends Command
{
    protected $signature = 'reminders:dispatch';

    protected $description = 'Dispatch ReminderTriggered for Reminder Rules whose target time has arrived';

    public function handle(): void
    {
        $dueRules = ReminderRule::whereNull('triggered_at')
            ->with('timelineEvent')
            ->get()
            ->filter(fn (ReminderRule $rule) => now()->greaterThanOrEqualTo(
                $rule->timelineEvent->scheduled_at->subMinutes($rule->offset_minutes)
            ));

        foreach ($dueRules as $rule) {
            ReminderTriggered::dispatch($rule);
            $rule->update(['triggered_at' => now()]);
        }

        $this->info("Dispatched {$dueRules->count()} reminder(s).");
    }
}
