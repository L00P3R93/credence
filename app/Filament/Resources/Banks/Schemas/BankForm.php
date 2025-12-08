<?php

namespace App\Filament\Resources\Banks\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BankForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
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
        ]);
    }
}
