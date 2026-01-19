<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\FaIcon;
use App\Models\Loan;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Loan Information')->schema([
                Grid::make(1)->schema([
                    TextEntry::make('customer.name')
                        ->label('Customer Name')
                        ->icon(FaIcon::USER)
                        ->columnSpanFull(),
                ])->columnSpanFull(),
                Grid::make(3)->schema([
                    TextEntry::make('loan.id')
                        ->label('Loan ID')
                        ->icon(FaIcon::HASHTAG),

                    TextEntry::make('loan.loan_amount')
                        ->label('Loan Amount')
                        ->money('KES')
                        ->icon(FaIcon::MONEY_BILL_WAVE)
                        ->formatStateUsing(fn ($state) => number_format($state)),

                    TextEntry::make('loan.loan_balance')
                        ->label('Loan Balance')
                        ->money('KES')
                        ->icon(FaIcon::WALLET)
                        ->color('danger')
                        ->getStateUsing(fn ($record) => $record->loan?->loanBalance() ?? 0)
                        ->formatStateUsing(fn ($state) => number_format($state)),
                ]),
            ])->columnSpanFull(),

            Section::make('Payment Details')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('amount')
                        ->money('KES')
                        ->icon(FaIcon::MONEY_BILL_WAVE)
                        ->formatStateUsing(fn ($state) => number_format($state)),

                    TextEntry::make('payment_method')
                        ->badge()
                        ->icon(FaIcon::CREDIT_CARD),

                    TextEntry::make('receipt_no')
                        ->icon(FaIcon::RECEIPT),
                ]),

                Grid::make(3)->schema([
                    TextEntry::make('date_received')
                        ->dateTime('d M, Y h:i A')
                        ->icon(FaIcon::CALENDAR),

                    TextEntry::make('status')
                        ->badge()
                        ->icon(FaIcon::CIRCLE_CHECK),
                ]),
            ])->columnSpanFull(),
        ]);
    }
}
