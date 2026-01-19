<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Models\Loan;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;

class LoanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->schema([
                Section::make('Loan Information')
                    ->description('Basic loan details')
                    ->icon('heroicon-s-document-currency-dollar')
                    ->columnSpan(2)
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('customer.name')
                                    ->label('Customer')
                                    ->icon('heroicon-o-user'),
                                TextEntry::make('status')
                                    ->badge(),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('given_date')
                                    ->date()
                                    ->icon('hugeicons-calendar-02'),
                                TextEntry::make('due_date')
                                    ->date()
                                    ->icon('hugeicons-calendar-upload-01')
                                    ->color(fn (Loan $record): string => $record->due_date->isPast() ? 'danger' : 'gray'),
                                TextEntry::make('loan_period')
                                    ->suffix(' Month(s)')
                                    ->icon('heroicon-o-clock'),
                            ]),

                        Grid::make(3)->schema([
                            TextEntry::make('bank.name')
                                ->label('Bank')
                                ->icon('heroicon-o-building-office'),
                            TextEntry::make('bankBranch.name')
                                ->label('Bank Branch')
                                ->icon('heroicon-o-map-pin'),
                            TextEntry::make('product.name')
                                ->label('Product')
                                ->icon('heroicon-o-cube'),
                        ]),

                        Grid::make(3)->schema([
                            TextEntry::make('collectionAgent.name')
                                ->label('Collection Agent')
                                ->placeholder('Not assigned')
                                ->icon('heroicon-o-user'),
                            TextEntry::make('collectionOfficer.name')
                                ->label('Collection Officer')
                                ->placeholder('Not assigned')
                                ->icon('heroicon-o-shield-check'),
                        ]),
                    ]),

                Section::make('Financial Details')
                    ->description('Amounts and fees')
                    ->icon('heroicon-o-banknotes')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('loan_amount')
                            ->money('KES')
                            ->weight('font-bold')
                            ->icon('heroicon-o-currency-dollar')
                            ->formatStateUsing(fn ($state) => number_format($state)),

                        TextEntry::make('loan_interest')
                            ->money('KES')
                            ->icon('heroicon-o-chart-bar')
                            ->formatStateUsing(fn ($state) => number_format($state)),

                        Group::make()->schema([
                            TextEntry::make('processing_fee')
                                ->money('KES')
                                ->icon('heroicon-o-receipt-percent')
                                ->formatStateUsing(fn ($state) => number_format($state)),

                            TextEntry::make('amount_paid')
                                ->money('KES')
                                ->icon('heroicon-o-calculator')
                                ->weight('font-bold')
                                ->color('success')
                                ->getStateUsing(fn (Loan $record) => $record->payments()->sum('amount'))
                                ->formatStateUsing(fn ($state) => number_format($state)),
                        ])->columns(2),



                        Group::make()->schema([
                            TextEntry::make('loan_total')
                                ->money('KES')
                                ->weight('font-bold')
                                ->color('blue')
                                ->icon('heroicon-o-calculator')
                                ->formatStateUsing(fn ($state) => number_format($state)),

                            TextEntry::make('loan_balance')
                                ->label('Loan Balance')
                                ->money('KES')
                                ->weight('font-bold')
                                ->color('danger')
                                ->icon('heroicon-o-calculator')
                                ->getStateUsing(fn (Loan $record) => $record->loanBalance())
                                ->formatStateUsing(fn ($state) => number_format($state)),
                        ])->columns(2),
                    ]),

                Section::make('Administrative Information')
                    ->description('System records')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columnSpan(1)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('createdBy.name')
                                    ->label('Created By')
                                    ->placeholder('Not assigned')
                                    ->icon('heroicon-o-user-plus'),
                                TextEntry::make('created_at')
                                    ->dateTime()
                                    ->since()
                                    ->icon('heroicon-o-calendar'),
                                TextEntry::make('updated_at')
                                    ->dateTime()
                                    ->since()
                                    ->icon('heroicon-o-arrow-path'),
                            ]),
                    ]),

                Section::make('Remarks')
                    ->description('Additional notes')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->columnSpan(2)
                    ->schema([
                        TextEntry::make('remarks')
                            ->label('')
                            ->html()
                            ->prose()
                            ->extraAttributes(['class' => 'bg-gray-50 p-4 rounded-lg']),
                    ]),
            ]);
    }
}
