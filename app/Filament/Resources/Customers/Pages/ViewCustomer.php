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
                    'customer_name' => $customer->name,
                    'product_id' => $customer->product_id,
                    'product_name' => $customer->product->name,
                    'bank_id' => $customer->bank_id,
                    'bank_name' => $customer->bank->name,
                    'bank_branch_id' => $customer->bank_branch_id,
                    'bank_branch_name' => $customer->bankBranch->name,
                    'loan_limit' => $customer->loan_limit
                ]))
                ->visible(fn () => $customer->canGetNewLoan()),
        ];
    }
}
