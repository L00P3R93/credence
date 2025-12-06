<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserStatus;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')
                    ->label('Full name')
                    ->prefixIcon(Heroicon::User)
                    ->prefixIconColor('primary')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (callable $set, $state, callable $get) {
                        // Generate password when both name and phone are filled
                        $name = trim($state);
                        $phone = $get('phone');
                        if(filled($name) && filled($phone)) {
                            $firstName = Str::of($name)->before(' ')->lower()->ucfirst();
                            $generatedPassword = "{$firstName}@{$phone}";
                            $set('password', $generatedPassword);
                            $set('password_confirmation', $generatedPassword);
                        }
                    })
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->autocomplete(false)
                    ->unique(ignoreRecord: true)
                    ->prefixIcon(Heroicon::AtSymbol)
                    ->prefixIconColor('primary')
                    ->validationMessages([
                        'email' => 'Invalid email address.',
                        'required' => 'Email address is required.',
                        'unique' => 'This email address is already in use.'
                    ])
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->telRegex('/^(?:\+254|254|0)(7\d{8}|1\d{8})$/')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (callable $set, $state, callable $get) {
                        // Generate password when both name and phone are filled
                        $name = $get('name');
                        $phone = trim($state);
                        if(filled($name) && filled($phone)) {
                            $firstName = Str::of($name)->before(' ')->lower()->ucfirst();
                            $generatedPassword = "{$firstName}@{$phone}";
                            $set('password', $generatedPassword);
                            $set('password_confirmation', $generatedPassword);
                        }
                    })
                    ->prefixIcon(Heroicon::Phone)
                    ->prefixIconColor('primary')
                    ->validationMessages([
                        'required' => 'Phone number is required.',
                        'regex' => 'Invalid phone number.'
                    ])
                    ->required(),
                Select::make('status')
                    ->label('User Status')
                    ->options(UserStatus::class)
                    ->default('active')
                    ->prefixIcon(Heroicon::ShieldExclamation)
                    ->prefixIconColor('primary')
                    ->native(false)
                    ->required(),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->label('User Roles')
                    ->preload()
                    ->multiple()
                    ->native(false)
                    ->searchable()
                    ->required(),

                Section::make('Password')->schema([
                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->required(fn (string $context) => $context === 'create')
                        ->dehydrated(fn ($state) => filled($state)) // only send if filled
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null) // hash if filled
                        ->default(fn (callable $get) => function () use ($get) {
                            $name = $get('name');
                            $phone = $get('phone');
                            if (filled($name) && filled($phone)) {
                                $firstName = Str::of($name)->before(' ')->lower()->ucfirst();
                                return "{$firstName}@{$phone}";
                            }
                            return null;
                        })
                        ->rules([
                            'confirmed',
                            'regex:/^(?=.*[A-Z])(?=.*[\W_]).{8,}$/',
                        ])
                        ->validationMessages([
                            'regex' => 'Password must be at least 8 characters long, contain at least one uppercase letter, and one special symbol.',
                            'confirmed' => 'Password confirmation does not match.',
                            'required' => 'Password is required.',
                        ]),
                    TextInput::make('password_confirmation')
                        ->label('Confirm Password')
                        ->password()
                        ->dehydrated(false) // don't send to DB
                        ->required(fn (string $context) => $context === 'create'),
                ])->columnSpanFull()->hidden(fn (?User $record) => $record !== null),
            ])->columns(2)->columnSpan(['lg' => 3]),
        ])->columns(3);
    }
}
