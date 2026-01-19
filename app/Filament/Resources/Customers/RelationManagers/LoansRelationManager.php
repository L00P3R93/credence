<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Filament\Resources\Loans\LoanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class LoansRelationManager extends RelationManager
{
    protected static string $relationship = 'loans';
    protected static ?string $title = 'Loans History';

    protected static ?string $relatedResource = LoanResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
