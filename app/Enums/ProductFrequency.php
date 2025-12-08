<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ProductFrequency: string implements HasLabel, HasIcon, HasColor
{
	case PAYDAY = 'payday';
	case WEEKLY = 'weekly';
	case MONTHLY = 'monthly';
	case QUARTERLY = 'quarterly';
	case ANNUALLY = 'annually';

	public function getLabel(): string
	{
        return match($this) {
            self::PAYDAY => 'Payday',
            self::WEEKLY => 'Weekly',
            self::MONTHLY => 'Monthly',
            self::QUARTERLY => 'Quarterly',
            self::ANNUALLY => 'Annually',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::PAYDAY => 'primary',
            self::WEEKLY => 'warning',
            self::MONTHLY => 'success',
            self::QUARTERLY => 'danger',
            self::ANNUALLY => 'info',
        };
    }

    public function getIcon(): BackedEnum
    {
        return match($this) {
            self::PAYDAY, self::WEEKLY, self::MONTHLY, self::QUARTERLY, self::ANNUALLY => Heroicon::OutlinedCalendar,
        };
    }
}
