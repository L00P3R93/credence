<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum LoanStatus: string implements HasLabel, HasIcon, HasColor
{
    case PENDING_VERIFICATION = 'pending_verification';
    case PENDING_CONFIRMATION = 'pending_confirmation';
    case PENDING_APPROVAL = 'pending_approval';
    case PENDING_DISBURSEMENT = 'pending_disbursement';
    case DISBURSED = 'disbursed';
    case OVERDUE = 'overdue';
    case PAST_OVERDUE = 'past_overdue';
    case CANCELED = 'canceled';
    case WRITTEN_OFF = 'written_off';
    case FRAUD = 'fraud';
    case DELETED = 'deleted';
    case CLEARED = 'cleared';

	public function getLabel(): string
	{
        return match ($this) {
            self::PENDING_VERIFICATION => 'Pending Verification',
            self::PENDING_CONFIRMATION => 'Pending Confirmation',
            self::PENDING_APPROVAL => 'Pending Approval',
            self::PENDING_DISBURSEMENT => 'Pending Disbursement',
            self::DISBURSED => 'Disbursed',
            self::OVERDUE => 'Overdue',
            self::PAST_OVERDUE => 'Past Overdue',
            self::CANCELED => 'Canceled',
            self::WRITTEN_OFF => 'Written Off',
            self::FRAUD => 'Fraud',
            self::DELETED => 'Deleted',
            self::CLEARED => 'Cleared',

        };
	}

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING_VERIFICATION => 'secondary',
            self::PENDING_CONFIRMATION => 'blue',
            self::PENDING_APPROVAL => 'purple',
            self::PENDING_DISBURSEMENT => 'info',
            self::DISBURSED => 'success',
            self::OVERDUE => 'warning',
            self::PAST_OVERDUE => 'danger',
            self::CANCELED, self::WRITTEN_OFF, self::FRAUD, self::DELETED, self::CLEARED => 'gray',
        };
    }

    public function getIcon(): BackedEnum
    {
        return match ($this) {
            self::PENDING_VERIFICATION, self::PENDING_CONFIRMATION, self::PENDING_APPROVAL, self::PENDING_DISBURSEMENT => Heroicon::OutlinedClock,
            self::DISBURSED => Heroicon::OutlinedCheckBadge,
            self::OVERDUE, self::PAST_OVERDUE => FaIcon::CALENDAR_TIMES_REGULAR,
            self::CANCELED,  => Heroicon::OutlinedXMark,
            self::WRITTEN_OFF, self::FRAUD => Heroicon::OutlinedShieldExclamation,
            self::DELETED => Heroicon::OutlinedTrash,
            self::CLEARED => Heroicon::OutlinedCheckCircle,
        };
    }
}
