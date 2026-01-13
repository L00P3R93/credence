<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Enums\LoanStatus;
use App\Models\Customer;
use App\Models\Loan;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class LoanForm
{
    public static function configure(Schema $schema): Schema
    {
        $customerId = request()->input('customer_id');
        $customer = $customerId ? Customer::find($customerId) : null;
        $productId = $customer?->product_id;
        $bankId = $customer?->bank_id;
        $bankBranchId = $customer?->bank_branch_id;
        $loanLimit = $customer?->loan_limit;

        $loanPeriod = [];
        // Loan Period array for 4 months
        for ($i = 1; $i <= 4; $i++) {
            $loanPeriod[$i] = "$i Month(s)";
        }
        return $schema
            ->components([
                Section::make('Customer Information')->schema([
                    Select::make('customer_id')
                        ->label('Customer')
                        ->relationship('customer', 'name')
                        ->disabled()
                        ->default($customerId),
                    Select::make('product_id')
                        ->label('Loan Product')
                        ->relationship('product', 'name')
                        ->disabled()
                        ->default($productId),
                    Select::make('bank_id')
                        ->label('Bank')
                        ->relationship('bank', 'name')
                        ->disabled()
                        ->default($bankId),
                    Select::make('bank_branch_id')
                        ->label('Bank Branch')
                        ->relationship('bankBranch', 'name')
                        ->disabled()
                        ->default($bankBranchId),
                    TextInput::make('loan_limit')
                        ->label('Loan Limit')
                        ->numeric()
                        ->disabled()
                        ->default($loanLimit),


                    Hidden::make('customer_id')
                        ->default($customerId),
                    Hidden::make('product_id')
                        ->default($productId),
                    Hidden::make('bank_id')
                        ->default($bankId),
                    Hidden::make('bank_branch_id')
                        ->default($bankBranchId),
                ])->columns(2)->columnSpanFull(),

                Section::make('Loan Details')->schema([
                    TextInput::make('loan_amount')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(fn (Get $get) => $get('loan_limit'))
                        ->reactive()
                        ->rules([
                            'required',
                            'numeric',
                            'min:5000',
                        ])
                        ->validationMessages([
                            'max' => 'Loan amount cannot exceed the customer\'s loan limit',
                            'min' => 'Loan amount cannot be less than 5000',
                            'required' => 'Loan amount is required',
                            'numeric' => 'Loan amount must be a number',
                        ])
                        ->dehydrateStateUsing(fn ($state) => (float) $state),
                    Select::make('loan_period')
                        ->options($loanPeriod)
                        ->native(false)
                        ->searchable()
                        ->required()
                        ->default(1),

                    Toggle::make('this_due')
                        ->label('Due This Month')
                        ->default(true) // Default to this month
                        ->reactive()
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            if ($get('this_due')) {
                                $set('next_due', false);
                            }
                        }),

                    Toggle::make('next_due')
                        ->label('Due Next Month')
                        ->default(false)
                        ->reactive()
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            if ($get('next_due')) {
                                $set('this_due', false);
                            }
                        }),

                    Select::make('agent')
                        ->label('Sales Agent Portfolio')
                        ->relationship(name: 'salesAgent', titleAttribute: 'name')
                        ->searchable()
                        ->preload()
                        ->default($customer?->user?->id)
                        ->native(false)
                        ->hidden(fn () => $customer?->loans?->whereNotIn('status', ['cancelled', 'deleted'])->count() > 0)
                        ->required(),

                    Select::make('temp_agent')
                        ->label('Sales Agent This Month')
                        ->relationship(name: 'tempAgent', titleAttribute: 'name')
                        ->searchable()
                        ->preload()
                        ->default($customer?->user?->id)
                        ->native(false)
                        ->hidden(fn () => $customer?->loans?->whereNotIn('status', ['cancelled', 'deleted'])->count() <= 0)
                        ->required(),
                ])->columns(2)->columnSpanFull(),

                Section::make('Loan Edit')->schema([
                    DatePicker::make('given_date')
                        ->native(false)
                        ->required(),
                    DatePicker::make('due_date')
                        ->native(false)
                        ->required(),
                    TextInput::make('loan_interest')
                        ->required()
                        ->numeric()
                        ->default(0.0)
                        ->step(500)
                    ->minValue(0)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, $get){
                        $loan_amount = (float) $get('loan_amount');
                        $loan_interest = (float) $state;
                        $loan_period = (int) $get('loan_period');
                        $processing_fee = round($loan_amount * 0.05);
                        $installment = round($loan_amount / $loan_period);
                        $loan_total = $installment * $loan_period;
                        $set('loan_total', $loan_total);
                        $set('processing_fee', $processing_fee);
                    }),
                    TextInput::make('processing_fee')
                        ->required()
                        ->numeric()
                        ->default(0.0),
                    TextInput::make('loan_total')
                        ->required()
                        ->numeric()
                        ->default(0.0),
                ])->visible(fn (?Loan $record) => $record !== null)->columns(2)->columnSpanFull()
            ]);
    }
}
