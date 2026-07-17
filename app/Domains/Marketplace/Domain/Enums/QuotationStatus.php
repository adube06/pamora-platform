<?php

namespace App\Domains\Marketplace\Domain\Enums;

enum QuotationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Awaiting Response',
            self::Submitted => 'Submitted',
            self::Accepted => 'Accepted',
            self::Rejected => 'Rejected',
            self::Expired => 'Expired',
        };
    }
}
