<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\CustomerStatus;
use App\Enums\Gender;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('lead_id')
                    ->relationship('lead', 'name')
                    ->default(null),
                TextInput::make('name')
                    ->required(),
                TextInput::make('id_no')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('phone_alt')
                    ->tel()
                    ->default(null),
                Select::make('gender')
                    ->options(Gender::class)
                    ->default('m')
                    ->required(),
                DatePicker::make('dob'),
                TextInput::make('work_email')
                    ->email()
                    ->default(null),
                TextInput::make('personal_email')
                    ->email()
                    ->default(null),
                Select::make('status')
                    ->options(CustomerStatus::class)
                    ->default('active')
                    ->required(),
                TextInput::make('loan_limit')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Textarea::make('comments')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('collection_comments')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('has_loan')
                    ->required(),
                Toggle::make('has_cheques')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                Select::make('bank_id')
                    ->relationship('bank', 'name')
                    ->required(),
                Select::make('bank_branch_id')
                    ->relationship('bankBranch', 'name')
                    ->default(null),
            ]);
    }
}
