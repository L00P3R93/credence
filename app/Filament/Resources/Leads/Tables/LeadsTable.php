<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Enums\FaIcon;
use App\Enums\LeadStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadsTable
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
                TextColumn::make('product.name')
                    ->badge()
                    ->searchable(),
                TextColumn::make('bank.name')
                    ->searchable(),
                TextColumn::make('bankBranch.name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('Added By')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
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
                    ->options(LeadStatus::class)
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
