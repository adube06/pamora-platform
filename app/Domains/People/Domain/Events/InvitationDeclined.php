<?php

namespace App\Domains\People\Domain\Events;

use App\Domains\People\Domain\Models\Invitation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvitationDeclined
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Invitation $invitation) {}
}
