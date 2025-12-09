<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum Gender: string implements HasLabel, HasIcon, HasColor
{
    case MALE = 'm';
    case FEMALE = 'f';

	public function getLabel(): string
	{
        return match ($this) {
            self::MALE => 'Male',
            self::FEMALE => 'Female',
        };
	}

    public function getColor(): string
    {
        return match ($this) {
            self::MALE => 'primary',
            self::FEMALE => 'secondary',
        };
    }

    public function getIcon(): BackedEnum
    {
        return match ($this) {
            self::MALE => FaIcon::MALE,
            self::FEMALE => FaIcon::FEMALE,
        };
    }
}
