<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductFrequency;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')
                    ->required(),
                TextInput::make('description')
                    ->required(),
                TextInput::make('rate')
                    ->required()
                    ->numeric()
                    ->default(0.25),
                Select::make('frequency')
                    ->options(ProductFrequency::class)
                    ->native(false)
                    ->searchable()
                    ->default('payday')
                    ->required(),
            ])->columns()->columnSpanFull(),
            Section::make()->schema([
                Toggle::make('rolls_over')
                    ->default(true)
                    ->required(),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ])->columns()->columnSpanFull()
        ]);
    }
}
