<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Filament\Resources\Loans\Actions\ApproveLoanAction;
use App\Filament\Resources\Loans\Actions\ClearLoanAction;
use App\Filament\Resources\Loans\Actions\ConfirmLoanAction;
use App\Filament\Resources\Loans\Actions\DisburseLoanAction;
use App\Filament\Resources\Loans\Actions\MarkDisbursedAction;
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
            ApproveLoanAction::make('approve_loan'),
            DisburseLoanAction::make('disburse_loan'),
            MarkDisbursedAction::make('mark_disbursed'),
            ClearLoanAction::make('clear_loan'),
            EditAction::make(),
        ];
    }
}
