<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')
                    ->label('Role Name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'required' => 'Role name is required.',
                        'unique' => 'This role name is already in use.'
                    ])
                    ->maxLength(255),
                Select::make('permissions')
                    ->label('Permissions')
                    ->relationship('permissions', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->native(false)
                    ->createOptionAction(fn (Action $action) => $action->visible(auth()->user()->can('create_permissions')))
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Permission Name')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->validationMessages([
                        'required' => 'At least one permission is required.',
                    ])
                    ->required(),
            ])->columnSpan(['lg' =>  3]),
        ])->columns(3);
    }
}
