<?php

namespace App\Domains\Finance\Domain\Enums;

enum PledgeStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Cancelled => 'Cancelled',
            self::Expired => 'Expired',
        };
    }
}
