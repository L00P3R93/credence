<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Enums\CustomerStatus;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Loans\LoanResource;
use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        $customer = $this->getRecord();
        return [
            EditAction::make(),
            Action::make('give_loan')
                ->label('Give Loan')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->url(LoanResource::getUrl('create', [
                    'customer_id' => $customer->id,
                ]))
                ->visible(fn () => $customer->canGetNewLoan()),
        ];
    }
}
