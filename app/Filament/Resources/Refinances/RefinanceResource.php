<?php

namespace App\Filament\Resources\Refinances;

use App\Filament\Resources\Refinances\Pages\CreateRefinance;
use App\Filament\Resources\Refinances\Pages\EditRefinance;
use App\Filament\Resources\Refinances\Pages\ListRefinances;
use App\Filament\Resources\Refinances\Schemas\RefinanceForm;
use App\Filament\Resources\Refinances\Tables\RefinancesTable;
use App\Models\Refinance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RefinanceResource extends Resource
{
    protected static ?string $model = Refinance::class;

    protected static string|BackedEnum|null $navigationIcon = 'hugeicons-move-top';
    protected static string | UnitEnum | null $navigationGroup = 'Loan Management';
    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'amount';

    public static function form(Schema $schema): Schema
    {
        return RefinanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RefinancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRefinances::route('/'),
            'create' => CreateRefinance::route('/create'),
            'edit' => EditRefinance::route('/{record}/edit'),
        ];
    }
}
