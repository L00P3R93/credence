<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Filament\Resources\Loans\LoanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;

class EditLoan extends EditRecord
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Recalculate totals if loan amount or loan_period or loan_interest changes
        if ($this->record->loan_amount != $data['loan_amount'] || $this->record->loan_period != $data['loan_period'] || $this->record->loan_interest != $data['loan_interest']) {
            $modelClass = $this->getResource()::getModel();
            $loan = new $modelClass;
            $loan->fill($data);
            $loan->calculate($data['next_due'], $data['this_due']);
            $userName = auth()->user()->name;
            $data = array_merge($data, [
                'loan_total' => $loan->loan_total,
                'processing_fee' => $loan->processing_fee,
                'loan_interest' => $loan->loan_interest,
                'remarks' => $this->record->remarks . "<br>Loan Updated by {$userName} on ".now()->format('Y-m-d H:i:s'),
            ]);
            $data = Arr::except($data, ['interest_rate', 'this_due', 'next_due']);
        }
        return $data;
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
