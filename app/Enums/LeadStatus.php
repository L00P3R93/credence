<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum LeadStatus:string implements HasLabel, HasColor, HasIcon
{
    case LEAD = 'lead';
    case BLOCKED = 'blocked';
    case CONVERTED = 'converted';

	public function getLabel(): string
	{
        return match($this) {
            self::LEAD => 'Lead',
            self::BLOCKED => 'Blocked',
            self::CONVERTED => 'Converted',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::LEAD => 'info',
            self::BLOCKED => 'danger',
            self::CONVERTED => 'success',
        };
    }

    public function getIcon(): BackedEnum
    {
        return match ($this) {
            self::LEAD => FaIcon::QUESTION_CIRCLE,
            self::BLOCKED => FaIcon::BAN,
            self::CONVERTED => FaIcon::USER_TAG,
        };
    }
}
