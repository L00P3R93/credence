<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Models\Loan;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LoanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer.name')
                    ->label('Customer'),
                TextEntry::make('given_date')
                    ->date(),
                TextEntry::make('due_date')
                    ->date(),
                TextEntry::make('loan_amount')
                    ->numeric(),
                TextEntry::make('loan_interest')
                    ->numeric(),
                TextEntry::make('processing_fee')
                    ->numeric(),
                TextEntry::make('loan_total')
                    ->numeric(),
                TextEntry::make('loan_period')
                    ->numeric(),
                IconEntry::make('new_loan')
                    ->boolean(),
                IconEntry::make('old_loan')
                    ->boolean(),
                IconEntry::make('top_up')
                    ->boolean(),
                TextEntry::make('agent')
                    ->numeric(),
                TextEntry::make('temp_agent')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_by')
                    ->numeric(),
                TextEntry::make('collection_agent')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('collection_officer')
                    ->numeric(),
                TextEntry::make('product.name')
                    ->label('Product'),
                TextEntry::make('bank.name')
                    ->label('Bank'),
                TextEntry::make('bankBranch.name')
                    ->label('Bank branch'),
                TextEntry::make('remarks')
                    ->label('Remarks')
                    ->html(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Loan $record): bool => $record->trashed()),
            ]);
    }
}
