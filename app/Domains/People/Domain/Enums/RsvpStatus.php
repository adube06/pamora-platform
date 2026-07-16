<?php

namespace App\Domains\People\Domain\Enums;

enum RsvpStatus: string
{
    case Attending = 'attending';
    case NotAttending = 'not_attending';
    case Maybe = 'maybe';

    public function label(): string
    {
        return match ($this) {
            self::Attending => 'Attending',
            self::NotAttending => 'Not Attending',
            self::Maybe => 'Maybe',
        };
    }
}
