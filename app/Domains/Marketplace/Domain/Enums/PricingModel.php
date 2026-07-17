<?php

namespace App\Domains\Marketplace\Domain\Enums;

enum PricingModel: string
{
    case Fixed = 'fixed';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Fixed Price',
            self::Custom => 'Custom Quote',
        };
    }
}
