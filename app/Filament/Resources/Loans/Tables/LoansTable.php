<?php

namespace App\Filament\Resources\Loans\Tables;

use App\Enums\CustomerStatus;
use App\Enums\LoanStatus;
use App\Models\Loan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LoansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                auth()->user()->isAdmin()
                    ? $query->with('customer')
                    : $query->where('agent', auth()->id())->orWhere('temp_agent', auth()->id());
            })
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('Loan Ref')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('given_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('loan_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('loan_total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                // Show Product and Bank together eg 'Product | Bank'
                TextColumn::make('Product Details')
                    ->label('Product | Bank')
                    ->state(function (Loan $record) {
                        return $record->product->name . ' | ' . $record->bank->name;
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name', fn (Builder $query) => $query->orderBy('name')->limit(10))
                    ->native(false)
                    ->preload()
                    ->searchable(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(LoanStatus::class)
                    ->native(false)
                    ->searchable(),
                SelectFilter::make('bank_id')
                    ->label('Bank')
                    ->relationship('bank', 'name', fn (Builder $query) => $query->orderBy('name')->limit(10))
                    ->native(false)
                    ->preload()
                    ->searchable()
            ])
            ->recordActions([
                ViewAction::make()->icon(Heroicon::OutlinedEye)->iconButton()->color('primary')->tooltip('View Loan'),
                EditAction::make()->icon(Heroicon::OutlinedPencil)->iconButton()->color('warning')->tooltip('Edit Loan'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Group::make('given_date')
                    ->label('Date Given')
                    ->date()
                    ->collapsible()
            ]);
    }
}
