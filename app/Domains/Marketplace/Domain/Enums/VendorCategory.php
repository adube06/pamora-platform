<?php

namespace App\Domains\Marketplace\Domain\Enums;

enum VendorCategory: string
{
    case Photography = 'photography';
    case Dj = 'dj';
    case Mc = 'mc';
    case Catering = 'catering';
    case Decoration = 'decoration';
    case Makeup = 'makeup';
    case LiveBand = 'live_band';

    public function label(): string
    {
        return match ($this) {
            self::Photography => 'Photography',
            self::Dj => 'DJ',
            self::Mc => 'MC',
            self::Catering => 'Catering',
            self::Decoration => 'Decoration',
            self::Makeup => 'Makeup',
            self::LiveBand => 'Live Band',
        };
    }
}
