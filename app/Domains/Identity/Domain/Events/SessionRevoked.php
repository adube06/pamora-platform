<?php

namespace App\Domains\Identity\Domain\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Matches the Identity PRD's Domain Events section exactly
 * (pamora-foundation/02-product/prd/09-identity.md).
 */
class SessionRevoked
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $sessionId,
        public readonly User $actor,
    ) {}
}
