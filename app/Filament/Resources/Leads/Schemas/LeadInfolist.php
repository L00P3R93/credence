<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Enums\FaIcon;
use App\Models\Customer;
use App\Models\Lead;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('')->schema([
                    TextEntry::make('name')
                        ->label('Full Name')
                        ->icon(FaIcon::USER),
                    TextEntry::make('id_no')
                        ->label('National ID/Passport No')
                        ->icon(FaIcon::ID_CARD)
                        ->copyable()
                        ->copyMessage('Phone Number copied to clipboard'),
                    TextEntry::make('phone')
                        ->label('Primary Phone Number')
                        ->icon(FaIcon::PHONE_SQUARE)
                        ->copyable()
                        ->copyMessage('Phone Number copied to clipboard'),
                    TextEntry::make('phone_alt')
                        ->label('Secondary Phone Number(s)')
                        ->icon(FaIcon::PHONE_SQUARE_ALT)
                        ->placeholder('Not Provided'),
                    TextEntry::make('gender')
                        ->icon(FaIcon::VENUS_MARS)
                        ->badge(),
                    TextEntry::make('dob')
                        ->icon(FaIcon::CALENDAR)
                        ->date('d-M-Y')
                        ->placeholder('Not Provided'),
                    TextEntry::make('work_email')
                        ->label('Work Email Address')
                        ->icon(Heroicon::AtSymbol)
                        ->placeholder('Not Provided')
                        ->copyable()
                        ->copyMessage('Email copied to clipboard'),
                    TextEntry::make('personal_email')
                        ->label('Personal Email Address')
                        ->icon(Heroicon::AtSymbol)
                        ->placeholder('Not Provided')
                        ->copyable()
                        ->copyMessage('Email copied to clipboard'),
                ])->columns(2)->columnSpanFull(),
                Section::make('Loan Product Details')->schema([
                    TextEntry::make('product.name')
                        ->label('Product')
                        ->icon(Heroicon::OutlinedBriefcase),
                    TextEntry::make('bank.name')
                        ->label('Bank')
                        ->icon(FaIcon::UNIVERSITY),
                    TextEntry::make('bankBranch.name')
                        ->label('Bank branch')
                        ->icon(FaIcon::HOTEL)
                        ->placeholder('Not Provided'),
                ])->columns(3)->columnSpanFull(),

                Section::make('System Info')->schema([
                    TextEntry::make('convertedToCustomer.name')
                        ->label('Converted to customer')
                        ->visible(fn (Lead $lead) => $lead->convertedToCustomer()->exists())
                        ->placeholder('Not Converted'),
                    TextEntry::make('converted_at')
                        ->dateTime()
                        ->visible(fn (Lead $lead) => $lead->convertedToCustomer()->exists())
                        ->placeholder('Not Converted'),

                    TextEntry::make('status')
                        ->label('Lead Status')
                        ->icon(FaIcon::SHIELD_ALT)
                        ->badge(),
                    TextEntry::make('user.name')
                        ->label('Added By')
                        ->icon(Heroicon::OutlinedUserPlus)
                        ->placeholder('System'),


                    TextEntry::make('created_at')
                        ->label('Created Date')
                        ->dateTime('M d, Y \a\t h:i A')
                        ->icon(Heroicon::OutlinedCalendar)
                        ->placeholder('Unknown'),
                    TextEntry::make('deleted_at')
                        ->label('Deleted Date')
                        ->dateTime('M d, Y \a\t h:i A')
                        ->icon(Heroicon::OutlinedTrash)
                        ->placeholder('Unknown')
                        ->visible(fn (Lead $record): bool => $record->trashed()),

                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
