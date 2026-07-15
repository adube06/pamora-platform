<?php

namespace App\Domains\People\Domain\Enums;

enum InvitationStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Expired = 'expired';
    case Revoked = 'revoked';
}
