<?php

namespace App\Domains\Finance\Domain\Enums;

enum ContributionMethod: string
{
    case Cash = 'cash';
    case MobileMoney = 'mobile_money';
    case BankTransfer = 'bank_transfer';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::MobileMoney => 'Mobile Money',
            self::BankTransfer => 'Bank Transfer',
            self::Other => 'Other',
        };
    }
}
