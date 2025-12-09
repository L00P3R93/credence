<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum CustomerStatus: string implements HasLabel, HasIcon, HasColor
{
    case ACTIVE = 'active';
    case BLOCKED = 'blocked';
    case BLACKLISTED = 'blacklisted';

	public function getLabel(): string
	{
		return match ($this) {
			self::ACTIVE => 'Active',
			self::BLOCKED => 'Blocked',
			self::BLACKLISTED => 'Blacklisted',
		};
	}

    public function getColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::BLOCKED => 'warning',
            self::BLACKLISTED => 'danger',
        };
    }

    public function getIcon(): BackedEnum
    {
        return match ($this) {
            self::ACTIVE => FaIcon::CHECK_CIRCLE_REGULAR,
            self::BLOCKED => FaIcon::EXCLAMATION_CIRCLE,
            self::BLACKLISTED => FaIcon::SHIELD_ALT,
        };
    }
}
