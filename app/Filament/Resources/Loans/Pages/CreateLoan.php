<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Filament\Resources\Loans\LoanResource;
use App\Models\Customer;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class CreateLoan extends CreateRecord
{
    protected static string $resource = LoanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['given_date'] = now();
        $data['due_date'] = now()->addMonth();
        $data['loan_period'] = 1; // Default to 1 month
        $data['status'] = 'pending_verification';
        $data['new_loan'] = true;
        $data['old_loan'] = false;
        $data['top_up'] = false;
        $data['created_by'] = auth()->id();

        // Remove the toggle fields as they're not in the database
        unset($data['this_due'], $data['next_due']);

        // Ensure customer_id is set from the URL parameter
        if (!isset($data['customer_id']) && request()->has('customer_id')) {
            $data['customer_id'] = request('customer_id');
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $customer = Customer::find($data['customer_id']);

        // Create the loan
        $loan = $customer->loans()->create($data);

        // Recalculate customer's loan status
        $customer->update([
            'has_loan' => true,
        ]);

        return $loan;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Create Loan')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success'),
            $this->getCancelFormAction()
                ->label('Cancel')
                ->icon(Heroicon::OutlinedXMark)
                ->color('danger'),
        ];
    }
}
