<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\FaIcon;
use App\Enums\PaymentMethod;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('phone')
                    ->label('Customer Phone')
                    ->tel()
                    ->telRegex('/^254[17]\d{8}$/')
                    ->prefixIcon(FaIcon::PHONE_SQUARE)
                    ->prefixIconColor('primary')
                    ->validationMessages([
                        'required' => 'Phone number is required.',
                        'regex' => 'Invalid phone number.',
                    ])
                    ->required(),
                TextInput::make('receipt_no')
                    ->label('Receipt No')
                    ->prefixIcon(FaIcon::FILE_INVOICE_DOLLAR)
                    ->prefixIconColor('primary')
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'required' => 'Receipt number is required.',
                        'unique' => 'Receipt number already exists.',
                    ])
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->prefixIcon(FaIcon::HASHTAG)
                    ->prefixIconColor('primary')
                    ->numeric()
                    ->default(0.0),
                Select::make('payment_method')
                    ->label('Payment Method')
                    ->prefixIcon(FaIcon::CREDIT_CARD)
                    ->prefixIconColor('primary')
                    ->options(PaymentMethod::class)
                    ->native(false)
                    ->required(),
                Select::make('loan_id')
                    ->label('Loan Ref')
                    ->prefixIcon(FaIcon::MONEY_BILL_WAVE)
                    ->prefixIconColor('primary')
                    ->relationship(name: 'loan', titleAttribute: 'id', modifyQueryUsing: fn (Builder $query) => $query->latest()->limit(10))
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "Loan #{$record->id} - {$record->customer->name}")
                    ->searchable()
                    ->native(false)
                    ->exists('loans', 'id')
                    ->validationMessages([
                        'required' => 'Loan ID is required.',
                        'exists' => 'Loan does not exist.',
                    ])
                    ->required(),
            ])->columns(2)->columnSpanFull()
        ]);
    }
}
