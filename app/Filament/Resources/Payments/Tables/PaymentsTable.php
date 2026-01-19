<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('loan_id')
                    ->label('Loan ID')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer.bank.name')
                    ->label('Bank')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount Paid')
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->sortable(),
                TextColumn::make('date_received')
                    ->label('Date Received')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->icon(Heroicon::OutlinedEye)->iconButton()->color('primary')->tooltip('View Payment'),
                EditAction::make()->icon(Heroicon::OutlinedPencilSquare)->iconButton()->color('warning')->tooltip('Edit Payment'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
