<?php

namespace App\Domains\Communication\Domain\Events;

use App\Domains\Communication\Domain\Models\ReminderRule;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReminderRuleScheduled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ReminderRule $reminderRule,
        public readonly User $actor,
    ) {}
}
