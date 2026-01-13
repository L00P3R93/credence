<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Filament\Resources\Loans\Actions\ConfirmLoanAction;
use App\Filament\Resources\Loans\Actions\VerifyLoanAction;
use App\Filament\Resources\Loans\LoanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLoan extends ViewRecord
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            VerifyLoanAction::make('verify_loan'),
            ConfirmLoanAction::make('confirm_loan'),
            EditAction::make(),
        ];
    }
}
