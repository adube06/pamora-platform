<?php

namespace App\Domains\Finance\Domain\Enums;

enum BudgetStatus: string
{
    case Active = 'active';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Closed => 'Closed',
        };
    }
}
