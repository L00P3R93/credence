<?php

namespace App\Filament\Resources\BankBranches;

use App\Filament\Resources\BankBranches\Pages\CreateBankBranch;
use App\Filament\Resources\BankBranches\Pages\EditBankBranch;
use App\Filament\Resources\BankBranches\Pages\ListBankBranches;
use App\Filament\Resources\BankBranches\Schemas\BankBranchForm;
use App\Filament\Resources\BankBranches\Tables\BankBranchesTable;
use App\Models\BankBranch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class BankBranchResource extends Resource
{
    protected static ?string $model = BankBranch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;
    protected static string | UnitEnum | null $navigationGroup = 'Banks & Branches';
    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BankBranchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BankBranchesTable::configure($table);
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
            'index' => ListBankBranches::route('/'),
            //'create' => CreateBankBranch::route('/create'),
            //'edit' => EditBankBranch::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
