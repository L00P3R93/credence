<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lead.name')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('id_no')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('phone_alt')
                    ->searchable(),
                TextColumn::make('gender')
                    ->badge()
                    ->searchable(),
                TextColumn::make('dob')
                    ->date()
                    ->sortable(),
                TextColumn::make('work_email')
                    ->searchable(),
                TextColumn::make('personal_email')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('loan_limit')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('has_loan')
                    ->boolean(),
                IconColumn::make('has_cheques')
                    ->boolean(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('product.name')
                    ->searchable(),
                TextColumn::make('bank.name')
                    ->searchable(),
                TextColumn::make('bankBranch.name')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
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
