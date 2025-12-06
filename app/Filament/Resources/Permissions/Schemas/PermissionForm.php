<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')
                    ->label('Permission Name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'required' => 'Permission name is required.',
                        'unique' => 'This permission name is already in use.',
                        'length' => 'Permission name must be between 2 and 255 characters.',
                    ])
                    ->maxLength(255),
                Select::make('roles')
                    ->label('Roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->native(false)
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Role Name')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->validationMessages([
                        'required' => 'At least one role is required.',
                    ])
                    ->required(),
            ])->columnSpan(['lg' =>  3]),
        ]);
    }
}
