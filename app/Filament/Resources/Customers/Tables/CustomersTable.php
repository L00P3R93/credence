<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Enums\CustomerStatus;
use App\Enums\FaIcon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                auth()->user()->isAdmin()
                    ? $query->with('user')
                    : $query->where('user_id', auth()->id());
            })
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('id_no')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone_alt')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gender')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('dob')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('work_email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('personal_email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('product.name'),
                TextColumn::make('bank.name'),
                TextColumn::make('bankBranch.name'),
                TextColumn::make('loan_limit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                IconColumn::make('has_loan')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('has_cheques')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('Added By')
                    ->toggleable(isToggledHiddenByDefault: true),

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
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name', fn (Builder $query) => $query->orderBy('name')->limit(10))
                    ->native(false)
                    ->preload()
                    ->searchable(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(CustomerStatus::class)
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
                ViewAction::make()->icon(FaIcon::EYE_REGULAR)->iconButton()->color('primary')->tooltip('View Customer'),
                EditAction::make()->icon(FaIcon::PENCIL_ALT)->iconButton()->color('warning')->tooltip('Edit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Group::make('created_at')
                    ->label('Date Created')
                    ->date()
                    ->collapsible()
            ]);
    }
}
