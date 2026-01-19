<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum PaymentStatus: string implements HasLabel, HasIcon, HasColor
{
    case COMPLETED = 'completed';
    case SUSPENDED = 'suspended';
    case FAILED = 'failed';
    case CLEARED = 'cleared';
    case UNDERPAYMENT = 'underpayment';
    case OVERPAYMENT = 'overpayment';

	public function getLabel(): string
	{
		return match ($this) {
			self::COMPLETED => 'Completed',
			self::SUSPENDED => 'Suspended',
			self::FAILED => 'Failed',
            self::CLEARED => 'Cleared',
            self::UNDERPAYMENT => 'Underpayment',
            self::OVERPAYMENT => 'Overpayment',
		};
	}

    public function getColor(): string
    {
        return match ($this) {
            self::COMPLETED, self::OVERPAYMENT => 'success',
            self::SUSPENDED, self::UNDERPAYMENT => 'warning',
            self::FAILED => 'danger',
            self::CLEARED => 'gray',
        };
    }

    public function getIcon(): BackedEnum
    {
        return match ($this) {
            self::COMPLETED, self::CLEARED, self::OVERPAYMENT => Heroicon::OutlinedCheckBadge,
            self::SUSPENDED, self::UNDERPAYMENT => Heroicon::OutlinedExclamationCircle,
            self::FAILED => Heroicon::OutlinedXCircle,
        };
    }
}
