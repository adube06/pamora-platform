<?php

namespace App\Domains\People\Domain\Enums;

/**
 * Purely descriptive, multi-value labels ("what someone does") — kept
 * deliberately separate from Role ("what someone may do"), per the
 * People PRD's Section 7 instruction that Responsibility and Permission
 * "must remain separate." A member may hold any number of these
 * alongside their single Role, and holding one grants no permissions.
 */
enum Responsibility: string
{
    case Chairperson = 'chairperson';
    case Treasurer = 'treasurer';
    case Secretary = 'secretary';
    case CateringLead = 'catering_lead';
    case LogisticsLead = 'logistics_lead';
    case DecorationLead = 'decoration_lead';
    case TransportCoordinator = 'transport_coordinator';

    public function label(): string
    {
        return match ($this) {
            self::Chairperson => 'Chairperson',
            self::Treasurer => 'Treasurer',
            self::Secretary => 'Secretary',
            self::CateringLead => 'Catering Lead',
            self::LogisticsLead => 'Logistics Lead',
            self::DecorationLead => 'Decoration Lead',
            self::TransportCoordinator => 'Transport Coordinator',
        };
    }
}
