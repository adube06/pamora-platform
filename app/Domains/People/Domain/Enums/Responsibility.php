<?php

namespace App\Domains\People\Domain\Enums;

enum Responsibility: string
{
    case Chairperson = 'chairperson';
    case Treasurer = 'treasurer';
    case Secretary = 'secretary';
    case LogisticsLead = 'logistics_lead';
    case FoodCoordinator = 'food_coordinator';

    public function label(): string
    {
        return match ($this) {
            self::Chairperson => 'Chairperson',
            self::Treasurer => 'Treasurer',
            self::Secretary => 'Secretary',
            self::LogisticsLead => 'Logistics Lead',
            self::FoodCoordinator => 'Food Coordinator',
        };
    }
}
