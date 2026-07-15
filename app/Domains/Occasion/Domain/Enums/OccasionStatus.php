<?php

namespace App\Domains\Occasion\Domain\Enums;

enum OccasionStatus: string
{
    case Draft = 'draft';
    case Planning = 'planning';
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Planning => 'Planning',
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::Archived => 'Archived',
            self::Cancelled => 'Cancelled',
        };
    }
}
