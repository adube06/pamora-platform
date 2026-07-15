<?php

namespace App\Domains\Occasion\Domain\Enums;

enum OccasionVisibility: string
{
    case Private = 'private';
    case InvitationOnly = 'invitation_only';
    case Public = 'public';

    public function label(): string
    {
        return match ($this) {
            self::Private => 'Private',
            self::InvitationOnly => 'Invitation Only',
            self::Public => 'Public',
        };
    }
}
