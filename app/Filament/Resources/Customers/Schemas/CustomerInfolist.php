<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('lead.name')
                    ->label('Lead')
                    ->placeholder('-'),
                TextEntry::make('name'),
                TextEntry::make('id_no'),
                TextEntry::make('phone'),
                TextEntry::make('phone_alt')
                    ->placeholder('-'),
                TextEntry::make('gender')
                    ->badge(),
                TextEntry::make('dob')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('work_email')
                    ->placeholder('-'),
                TextEntry::make('personal_email')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('loan_limit')
                    ->numeric(),
                TextEntry::make('comments')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('collection_comments')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('has_loan')
                    ->boolean(),
                IconEntry::make('has_cheques')
                    ->boolean(),
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('product.name')
                    ->label('Product'),
                TextEntry::make('bank.name')
                    ->label('Bank'),
                TextEntry::make('bankBranch.name')
                    ->label('Bank branch')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
