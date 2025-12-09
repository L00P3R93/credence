<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Leads\LeadResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('convertToCustomer')
                ->label('Convert to Customer')
                ->icon(Heroicon::OutlinedUserPlus)
                ->color('success')
                ->action(function () {
                    $customer = $this->record->convertToCustomer();

                    Notification::make()
                        ->title('Lead converted to customer successfully!')
                        ->success()
                        ->send();

                    return redirect(CustomerResource::getUrl('view', ['record' => $customer->id]));
                })
                ->visible(fn () => $this->record->status->value === 'lead')
                ->requiresConfirmation()
                ->modalHeading('Convert Lead to Customer')
                ->modalDescription('Are you sure you want to convert this lead to a customer? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, convert to customer'),
        ];
    }
}
