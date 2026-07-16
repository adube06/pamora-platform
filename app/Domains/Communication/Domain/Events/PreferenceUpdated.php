<?php

namespace App\Domains\Communication\Domain\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Matches the Communication PRD's Domain Events section exactly
 * (pamora-foundation/02-product/prd/06-communication.md).
 */
class PreferenceUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly User $actor,
    ) {}
}
