<?php

namespace App\Domains\Marketplace\Domain\Enums;

enum VendorVerificationStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Verified => 'Verified',
            self::Rejected => 'Rejected',
        };
    }
}
