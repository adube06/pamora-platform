<?php

namespace App\Domains\Communication\Domain\Events;

use App\Domains\Communication\Domain\Models\ReminderRule;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * System-triggered by the reminders:dispatch scheduled command — no
 * actor, matching MemberJoined's shape.
 */
class ReminderTriggered
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly ReminderRule $reminderRule) {}
}
