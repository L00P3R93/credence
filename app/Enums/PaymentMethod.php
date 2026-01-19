<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum PaymentMethod: string implements HasLabel, HasIcon, HasColor
{
    case M_PESA = 'm-pesa';
    case BANK_TRANSFER = 'bank_transfer';
    case CASH = 'cash';

    public function getLabel(): string
    {
        return match ($this) {
            self::M_PESA => 'M-Pesa',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CASH => 'Cash',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::M_PESA => 'success',
            self::BANK_TRANSFER => 'warning',
            self::CASH => 'yellow',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::M_PESA => 'hugeicons-send-to-mobile',
            self::BANK_TRANSFER => 'hugeicons-transaction',
            self::CASH => 'hugeicons-cash-02',
        };
    }
}
