<?php

namespace App\Domains\Marketplace\Domain\Enums;

enum BookingStatus: string
{
    case Requested = 'requested';
    case Accepted = 'accepted';
    case Confirmed = 'confirmed';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Declined = 'declined';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Requested',
            self::Accepted => 'Accepted',
            self::Confirmed => 'Confirmed',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Declined => 'Declined',
            self::Cancelled => 'Cancelled',
            self::Expired => 'Expired',
        };
    }
}
