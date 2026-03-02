<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Enums\LoanStatus;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

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
                        ->native(false)
                        ->searchable()
                        ->required()
                        ->default($customerId),
                    Select::make('product_id')
                        ->label('Loan Product')
                        ->relationship('product', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->live() // Reactive on change
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::updateInterestRate($get, $set);
                            self::calculateTotals($get, $set);
                        })
                        ->afterStateHydrated(function (Get $get, Set $set) {
                            self::updateInterestRate($get, $set);
                            self::calculateTotals($get, $set);
                        })
                        ->default($productId),
                    Select::make('bank_id')
                        ->label('Bank')
                        ->relationship('bank', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->default($bankId),
                    Select::make('bank_branch_id')
                        ->label('Bank Branch')
                        ->relationship('bankBranch', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->default($bankBranchId),
                    TextInput::make('loan_limit')
                        ->label('Loan Limit')
                        ->numeric()
                        ->disabled()
                        ->default($loanLimit),
                ])->columns(2)->columnSpanFull(),

                Section::make('Loan Details')->schema([
                    TextInput::make('loan_amount')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(fn (Get $get) => $get('loan_limit'))
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::calculateTotals($get, $set);
                        })
                        ->afterStateHydrated(function (Get $get, Set $set) {
                            if ($get('loan_amount')) {
                                self::calculateTotals($get, $set);
                            }
                        })
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
                        ]),
                    TextInput::make('interest_rate')
                        ->required()
                        ->numeric()
                        ->suffix('%')
                        ->disabled() // Auto-set from product
                        ->readOnly()
                        ->dehydrated(true) // Still save to database
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::calculateTotals($get, $set);
                        })
                        // ✅ Calculate when rate is hydrated (for edit mode)
                        ->afterStateHydrated(function (Get $get, Set $set) {
                            if ($get('interest_rate') && $get('loan_amount')) {
                                self::calculateTotals($get, $set);
                            }
                        }),
                    Select::make('loan_period')
                        ->options($loanPeriod)
                        ->native(false)
                        ->searchable()
                        ->required()
                        ->default(1)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            self::calculateTotals($get, $set);
                        })
                        ->afterStateHydrated(function (Get $get, Set $set) {
                            if ($get('loan_period')) {
                                self::calculateTotals($get, $set);
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
                Section::make('Loan Dates')->schema([
                    Toggle::make('this_due')
                        ->label('Due This Month')
                        ->live(),

                    Toggle::make('next_due')
                        ->label('Due Next Month')
                        ->live()
                    ,
                    DatePicker::make('given_date')
                        ->native(false)
                        ->required(),
                    DatePicker::make('due_date')
                        ->native(false)
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::updateDueToggles($get, $set);
                        })
                        ->afterStateHydrated(function (Get $get, Set $set) {
                            self::updateDueToggles($get, $set);
                        })
                        ->default(now()->addMonth()),
                ])->columns(2)->columnSpanFull(),
                Section::make('Calculated Fields')->schema([

                    TextInput::make('loan_interest')
                        ->required()
                        ->numeric()
                        ->prefix('KES')
                        ->live(),
                    TextInput::make('processing_fee')
                        ->required()
                        ->numeric()
                        ->prefix('KES')
                        ->live(),
                    TextInput::make('loan_total')
                        ->required()
                        ->numeric()
                        ->prefix('KES')
                        ->live(),
                ])->visible(fn (?Loan $record) => $record !== null)->columns(2)->columnSpanFull()
            ]);
    }

    /**
     * Fetch and set interest rate from selected product
     */
    private static function updateInterestRate(Get $get, Set $set): void
    {
        $productId = $get('product_id');

        if (!$productId) {
            $set('interest_rate', null);
            return;
        }

        $product = Product::find($productId);

        if ($product && $product->rate) {
            $set('interest_rate', $product->rate * 100);
        }
    }

    /**
     * Calculate loan_interest, processing_fee, and loan_total
     */
    private static function calculateTotals(Get $get, Set $set): void
    {
        $amount = floatval($get('loan_amount') ?? 0);
        $period = intval($get('loan_period') ?? 1);
        $rate = floatval($get('interest_rate') ?? 0);

        // Interest = (Amount × Rate% × Period) / 100
        $interest = ($amount * $rate * $period) / 100;

        // Processing fee: 2% of amount (or from product if available)
        $processingFee = $amount * 0.02;

        // Total = Principal + Interest + Processing Fee
        $total = $amount + $interest;

        $set('loan_interest', round($interest, 2));
        $set('processing_fee', round($processingFee, 2));
        $set('loan_total', round($total, 2));
    }

    /**
     * Update this_due and next_due toggles based on due_date
     */
    private static function updateDueToggles(Get $get, Set $set): void
    {
        $dueDate = $get('due_date');

        if (!$dueDate) {
            $set('this_due', false);
            $set('next_due', false);
            return;
        }

        $due = Carbon::parse($dueDate);

        $set('this_due', $due->isCurrentMonth());
        $set('next_due', $due->isNextMonth());
    }
}
