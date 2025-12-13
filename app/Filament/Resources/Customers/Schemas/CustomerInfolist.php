<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\CustomerStatus;
use App\Enums\FaIcon;
use App\Models\Customer;
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Personal Details')->schema([
                Grid::make(['default' => 1, 'lg' => 12])->schema([
                    // Left Column - Profile Photo with Card
                    Group::make()->schema([
                        Section::make()->schema([
                            ImageEntry::make('profile-photo')
                                ->label('Customer Photo')
                                ->circular()
                                ->getStateUsing(fn ($record) => $record->getFirstMediaUrl('profile-photo'))
                                ->defaultImageUrl(url('/images/avatar.png'))
                                ->extraAttributes(['class' => 'mx-auto mb-4']),

                            // Optional: Add some basic info under the photo
                            /*TextEntry::make('name')
                                ->label('Customer:')
                                ->size('text-lg')
                                ->weight('font-semibold')
                                ->extraAttributes(['class' => 'text-center'])
                                ->hidden(fn ($state) => empty($state)),*/
                        ])
                    ])->columnSpan(['default' => 'full', 'lg' => 3])->extraAttributes(['class' => 'lg:pr-6']),

                    // Right Column - Details in a Grid
                    Group::make()->schema([
                        Grid::make(['default' => 1, 'md' => 2])->schema([
                            TextEntry::make('name')
                                ->label('Full Name')
                                ->icon(FaIcon::USER)
                                ->size('text-lg')
                                ->weight('font-semibold'),

                            TextEntry::make('id_no')
                                ->label('National ID/Passport No')
                                ->icon(FaIcon::ID_CARD)
                                ->copyable()
                                ->copyMessage('ID Number copied to clipboard'),

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
                                ->label('Date of Birth')
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
                        ])
                    ])->columnSpan(['default' => 'full', 'lg' => 9])
                ])
            ])->columnSpanFull(),
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
            Section::make('Comments & Remarks')->schema([
                TextEntry::make('comments')
                    ->placeholder('No Comments'),
                TextEntry::make('collection_comments')
                    ->placeholder('No Comments'),
            ])->columns(2)->columnSpanFull()->collapsed(),
            Section::make('System Info')->schema([
                TextEntry::make('status')
                    ->label('Customer Status')
                    ->icon(FaIcon::SHIELD_ALT)
                    ->badge(),
                TextEntry::make('user.name')
                    ->label('Added By')
                    ->icon(Heroicon::OutlinedUserPlus)
                    ->placeholder('System'),

                IconEntry::make('has_loan')
                    ->boolean(),
                IconEntry::make('has_cheques')
                    ->boolean(),

                TextEntry::make('loan_limit')
                    ->label('Loan Limit')
                    ->icon(FaIcon::DOLLAR_SIGN)
                    ->numeric()
                    ->placeholder('Not Provided'),


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
                    ->visible(fn (Customer $record): bool => $record->trashed()),

            ])->columns(2)->columnSpanFull(),
        ]);
    }
}
