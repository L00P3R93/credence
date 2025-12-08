<?php

namespace App\Filament\Resources\BankBranches\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class BankBranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('bank_id')
                    ->label('Bank')
                    ->relationship(
                        name: 'bank',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->where('is_active', true)->orderBy('name', 'asc')->limit(10)
                    )
                    ->createOptionForm([
                        Section::make()->schema([
                            TextInput::make('name')
                                ->required(),
                            TextInput::make('payday')
                                ->default('23')
                                ->required(),
                            Toggle::make('is_active')
                                ->default(true)
                                ->required(),
                        ])->columns(2)->columnSpanFull()
                    ])
                    ->createOptionAction(function (Action $action) {
                        return $action->modalHeading('Create Bank')->modalSubmitActionLabel('Create Bank');
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
