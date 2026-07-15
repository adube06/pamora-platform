<?php

namespace App\Domains\People\Domain\Enums;

enum OccasionMemberStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
}
