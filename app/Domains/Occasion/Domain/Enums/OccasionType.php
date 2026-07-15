<?php

namespace App\Domains\Occasion\Domain\Enums;

enum OccasionType: string
{
    case Wedding = 'wedding';
    case Funeral = 'funeral';
    case Birthday = 'birthday';

    public function label(): string
    {
        return match ($this) {
            self::Wedding => 'Wedding',
            self::Funeral => 'Funeral',
            self::Birthday => 'Birthday',
        };
    }
}
