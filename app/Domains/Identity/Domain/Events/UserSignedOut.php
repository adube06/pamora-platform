<?php

namespace App\Domains\Identity\Domain\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Matches the Identity PRD's Domain Events section
 * (pamora-foundation/02-product/prd/09-identity.md).
 */
class UserSignedOut
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly User $user) {}
}
