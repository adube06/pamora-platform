<?php

namespace App\Domains\Planning\Domain\Enums;

enum TaskStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Deferred = 'deferred';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Deferred => 'Deferred',
        };
    }
}
